"""Deterministic committer — applies APPROVED crm_change_proposals to EspoCRM.

This worker is the privileged second half of the propose -> approve -> commit gate.
It is strictly procedural: there is NO LLM in this path. Agents (any model) only ever
write proposals to the Supabase staging table; a human flips status to 'approved'; this
worker is the only thing that holds a live-CRM write key and applies those rows.

For every approved proposal it:
  1. resolves the target record (by espocrm_id, else by match_key on the entity's
     match field — momentum_client_id) to decide update vs insert,
  2. prepares the payload by ENFORCING per-entity field casing
     (Account = snake_case, Contact = camelCase; Policy/Opportunity pass through),
  3. pushes the write directly to the EspoCRM REST API,
  4. reads the record back and verifies every field it sent actually stuck
     (EspoCRM silently drops wrong-cased or ACL-blocked fields — see CLAUDE.md),
  5. records the outcome on the proposal row (status committed/failed, result, error).

Run it on a schedule or by hand. Dependencies: stdlib only (urllib, json).

Env:
  SUPABASE_URL            e.g. https://xxxx.supabase.co
  SUPABASE_SERVICE_KEY    service-role key (RLS on; service_role bypasses it)
  ESPOCRM_BASE_URL        e.g. https://crm.example.com
  ESPOCRM_API_KEY         API-user key with WRITE scope for the target entities
  COMMITTER_DRY_RUN       if "1"/"true", resolve + prepare but never write (default off)
  COMMITTER_BATCH_LIMIT   max proposals per run (default 50)
"""

from __future__ import annotations

import json
import logging
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from typing import Any

log = logging.getLogger("deterministic-committer")
logging.basicConfig(level=logging.INFO, format="%(asctime)s %(name)s %(levelname)s %(message)s")

SUPABASE_URL = os.environ.get("SUPABASE_URL", "").strip().rstrip("/")
SUPABASE_SERVICE_KEY = os.environ.get("SUPABASE_SERVICE_KEY", "").strip()
ESPOCRM_BASE_URL = os.environ.get("ESPOCRM_BASE_URL", "").strip().rstrip("/")
ESPOCRM_API_KEY = os.environ.get("ESPOCRM_API_KEY", "").strip()
DRY_RUN = os.environ.get("COMMITTER_DRY_RUN", "").strip().lower() in ("1", "true", "yes")
BATCH_LIMIT = max(1, min(int(os.environ.get("COMMITTER_BATCH_LIMIT", "50") or 50), 500))

ALLOWED_ENTITIES = ("Account", "Contact", "Policy", "Opportunity")

# Per-entity field-casing rule. EspoCRM silently drops fields written in the wrong
# casing for the entity, so the committer normalizes every payload key before writing.
#   Account  = snake_case   Contact = camelCase   Policy/Opportunity = leave as-is (mixed)
CASING_BY_ENTITY = {
    "Account": "snake",
    "Contact": "camel",
    "Policy": "passthrough",
    "Opportunity": "passthrough",
}

# Field used to dedup / match an existing record when no espocrm_id is supplied.
# The stored match_key holds the VALUE; the attribute name is cased per entity.
MATCH_FIELD_BY_ENTITY = {
    "Account": "momentum_client_id",
    "Contact": "momentumClientId",
    "Policy": "momentum_client_id",
    "Opportunity": "momentum_client_id",
}


class CommitError(Exception):
    """Raised when a proposal cannot be applied; message is stored on the row."""


# ---------------------------------------------------------------------------
# Casing helpers (deterministic, no I/O)
# ---------------------------------------------------------------------------

def _to_snake(key: str) -> str:
    # camelCase / PascalCase -> snake_case; already-snake keys are unchanged.
    s = re.sub(r"(.)([A-Z][a-z]+)", r"\1_\2", key)
    s = re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s)
    return s.replace("__", "_").lower()


def _to_camel(key: str) -> str:
    # snake_case -> camelCase; already-camel keys are unchanged.
    if "_" not in key:
        return key[:1].lower() + key[1:] if key else key
    head, *rest = key.split("_")
    return head.lower() + "".join(p[:1].upper() + p[1:] for p in rest if p)


def enforce_casing(entity: str, payload: dict[str, Any]) -> dict[str, Any]:
    """Return a new payload with keys normalized to the entity's casing rule."""
    rule = CASING_BY_ENTITY.get(entity, "passthrough")
    if rule == "snake":
        transform = _to_snake
    elif rule == "camel":
        transform = _to_camel
    else:
        return dict(payload)
    return {transform(k): v for k, v in payload.items()}


# ---------------------------------------------------------------------------
# HTTP helpers (stdlib urllib; mirrors the bridge's no-extra-deps style)
# ---------------------------------------------------------------------------

def _http(method: str, url: str, headers: dict[str, str], body: Any = None) -> Any:
    data = json.dumps(body).encode("utf-8") if body is not None else None
    req = urllib.request.Request(url=url, data=data, method=method, headers=headers)
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            raw = resp.read().decode("utf-8")
            return json.loads(raw) if raw else None
    except urllib.error.HTTPError as exc:
        detail = ""
        try:
            detail = exc.read().decode("utf-8")[:300]
        except Exception:
            pass
        raise CommitError(f"{method} {url} -> HTTP {exc.code} {detail}") from exc
    except urllib.error.URLError as exc:
        raise CommitError(f"{method} {url} -> {exc.reason}") from exc


def _supabase(method: str, path: str, params: dict[str, str] | None = None, body: Any = None) -> Any:
    url = f"{SUPABASE_URL}/rest/v1/{path}"
    if params:
        url += "?" + urllib.parse.urlencode(params)
    headers = {
        "apikey": SUPABASE_SERVICE_KEY,
        "Authorization": f"Bearer {SUPABASE_SERVICE_KEY}",
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Prefer": "return=representation",
    }
    return _http(method, url, headers, body)


def _espocrm(method: str, path: str, params: list[tuple[str, Any]] | None = None, body: Any = None) -> Any:
    url = f"{ESPOCRM_BASE_URL}/api/v1/{path}"
    if params:
        url += "?" + urllib.parse.urlencode(params)
    headers = {
        "X-Api-Key": ESPOCRM_API_KEY,
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    return _http(method, url, headers, body)


# ---------------------------------------------------------------------------
# Record resolution + write + read-back verification
# ---------------------------------------------------------------------------

def resolve_target_id(entity: str, espocrm_id: str | None, match_key: str | None) -> str | None:
    """Return an existing record id, or None if no match (insert path)."""
    if espocrm_id:
        return espocrm_id
    if not match_key:
        return None
    attr = MATCH_FIELD_BY_ENTITY.get(entity, "momentum_client_id")
    # EspoCRM v8 ignores a JSON `where`; use the bracket-encoded form.
    params = [
        ("maxSize", 2),
        ("select", "id"),
        ("where[0][type]", "equals"),
        ("where[0][attribute]", attr),
        ("where[0][value]", match_key),
    ]
    data = _espocrm("GET", entity, params=params)
    rows = data.get("list", []) if isinstance(data, dict) else []
    if not rows:
        return None
    if len(rows) > 1:
        raise CommitError(f"ambiguous match: {len(rows)} {entity} rows for {attr}={match_key}")
    return rows[0].get("id")


def write_record(entity: str, target_id: str | None, payload: dict[str, Any]) -> str:
    """POST (insert) or PUT (update) the payload. Returns the record id."""
    if target_id:
        result = _espocrm("PUT", f"{entity}/{target_id}", body=payload)
        return result.get("id", target_id) if isinstance(result, dict) else target_id
    result = _espocrm("POST", entity, body=payload)
    new_id = result.get("id") if isinstance(result, dict) else None
    if not new_id:
        raise CommitError("insert returned no id")
    return new_id


def _values_match(sent: Any, got: Any) -> bool:
    if sent is None:
        return True  # we did not assert a value
    if isinstance(sent, (dict, list)):
        return sent == got
    # EspoCRM may coerce numerics/strings; compare on stringified form.
    return str(sent) == str(got)


def verify_readback(entity: str, record_id: str, payload: dict[str, Any]) -> tuple[bool, dict[str, Any], list[str]]:
    """Re-read the record and confirm every sent field stuck (silent-drop guard)."""
    record = _espocrm("GET", f"{entity}/{record_id}")
    if not isinstance(record, dict):
        raise CommitError("read-back returned no record")
    dropped = [k for k, v in payload.items() if not _values_match(v, record.get(k))]
    readback = {k: record.get(k) for k in payload}
    return (len(dropped) == 0), readback, dropped


# ---------------------------------------------------------------------------
# Proposal lifecycle
# ---------------------------------------------------------------------------

def fetch_approved() -> list[dict[str, Any]]:
    return _supabase(
        "GET",
        "crm_change_proposals",
        params={
            "select": "*",
            "status": "eq.approved",
            "order": "created_at.asc",
            "limit": str(BATCH_LIMIT),
        },
    ) or []


def _mark(proposal_id: str, fields: dict[str, Any]) -> None:
    fields = {**fields, "updated_at": datetime.now(timezone.utc).isoformat()}
    _supabase("PATCH", "crm_change_proposals", params={"id": f"eq.{proposal_id}"}, body=fields)


def process(proposal: dict[str, Any]) -> str:
    pid = proposal["id"]
    entity = proposal.get("entity")
    if entity not in ALLOWED_ENTITIES:
        raise CommitError(f"unsupported entity {entity!r}")

    after = proposal.get("after") or {}
    if not isinstance(after, dict) or not after:
        raise CommitError("proposal has empty 'after' payload")

    op = proposal.get("op", "upsert")
    payload = enforce_casing(entity, after)
    target_id = resolve_target_id(entity, proposal.get("espocrm_id"), proposal.get("match_key"))

    if op in ("update", "enrich") and not target_id:
        raise CommitError(f"op={op} needs an existing record but none matched")

    if DRY_RUN:
        log.info("[dry-run] %s %s %s -> %s payload=%s", pid, op, entity, target_id or "INSERT", payload)
        return "dry-run"

    record_id = write_record(entity, target_id, payload)
    ok, readback, dropped = verify_readback(entity, record_id, payload)

    if not ok:
        _mark(pid, {
            "status": "failed",
            "espocrm_id": record_id,
            "result": readback,
            "error": f"silent field drop on read-back: {', '.join(dropped)}",
        })
        return f"failed (dropped: {', '.join(dropped)})"

    _mark(pid, {
        "status": "committed",
        "espocrm_id": record_id,
        "result": readback,
        "error": None,
        "committed_at": datetime.now(timezone.utc).isoformat(),
    })
    return "committed"


def _require_env() -> None:
    missing = [
        name for name, val in (
            ("SUPABASE_URL", SUPABASE_URL),
            ("SUPABASE_SERVICE_KEY", SUPABASE_SERVICE_KEY),
            ("ESPOCRM_BASE_URL", ESPOCRM_BASE_URL),
            ("ESPOCRM_API_KEY", ESPOCRM_API_KEY),
        ) if not val
    ]
    if missing:
        log.error("Missing required env: %s", ", ".join(missing))
        sys.exit(2)


def main() -> int:
    _require_env()
    proposals = fetch_approved()
    log.info("Fetched %d approved proposal(s)%s", len(proposals), " [DRY RUN]" if DRY_RUN else "")

    committed = failed = errored = 0
    for proposal in proposals:
        pid = proposal.get("id")
        try:
            outcome = process(proposal)
            log.info("proposal %s: %s", pid, outcome)
            if outcome == "committed":
                committed += 1
            elif outcome.startswith("failed"):
                failed += 1
        except CommitError as exc:
            errored += 1
            log.error("proposal %s errored: %s", pid, exc)
            if not DRY_RUN:
                try:
                    _mark(pid, {"status": "failed", "error": str(exc)})
                except CommitError as mark_exc:
                    log.error("could not mark proposal %s failed: %s", pid, mark_exc)

    log.info("Done. committed=%d failed=%d errored=%d", committed, failed, errored)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
