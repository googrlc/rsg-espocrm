#!/usr/bin/env python3
"""
Add Espo field `dbName` where snake_case metadata must read legacy camelCase
MySQL columns. Compares each target entityDefs JSON to entity-defs/<Entity>.json
(API dump) so we only set dbName when the camelCase name existed historically.

Do not use this for Account if MySQL pass4 account column renames were already
applied (see tools/migrations/pass4_account_rename_mysql8.sql): those installs
store snake_case column names, so dbName would incorrectly point at removed
camelCase columns. Policy and Renewal had no matching rename migration in-repo.
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ENTITY_DEFS = ROOT / "entity-defs"
CUSTOM = ROOT / "custom" / "Espo" / "Custom" / "Resources" / "metadata" / "entityDefs"

LINKISH = frozenset({"link", "linkMultiple", "file", "image", "attachmentMultiple"})


def snake_to_camel(name: str) -> str:
    parts = name.split("_")
    return parts[0] + "".join(p.title() for p in parts[1:])


def patch_entity(entity: str) -> int:
    dump_path = ENTITY_DEFS / f"{entity}.json"
    cur_path = CUSTOM / f"{entity}.json"
    if not dump_path.is_file() or not cur_path.is_file():
        return 0

    dump_fields = set(json.loads(dump_path.read_text())["entityDefs"][entity]["fields"])
    data = json.loads(cur_path.read_text())
    fields = data.get("fields", {})
    changed = 0

    for fname, fdef in list(fields.items()):
        if "_" not in fname or not isinstance(fdef, dict):
            continue
        if fdef.get("type") in LINKISH:
            continue
        camel = snake_to_camel(fname)
        if camel not in dump_fields:
            continue
        if fdef.get("dbName") == camel:
            continue
        fdef["dbName"] = camel
        fields[fname] = fdef
        changed += 1

    if changed:
        cur_path.write_text(json.dumps(data, indent=4) + "\n")
    return changed


def main() -> int:
    entities = sys.argv[1:] or ["Policy", "Renewal"]
    total = 0
    for ent in entities:
        n = patch_entity(ent)
        print(f"{ent}: added/updated dbName on {n} fields")
        total += n
    print(f"total fields: {total}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
