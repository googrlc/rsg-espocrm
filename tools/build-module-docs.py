#!/usr/bin/env python3
"""Regenerate field-reference/modules/*.md + README.md from live EspoCRM metadata.

Reproduces the original per-module "Field Guide" format. Inputs are a read-only
live pull (the API key is stale, so data comes from the metadata cache, which is
equivalent to GET /api/v1/Metadata + GET /api/v1/I18n):

  metadata/live-pull/docs_entitydefs.json  -> {entityDefs, scopes} per documented entity
  metadata/live-pull/docs_i18n.json        -> {entities, GlobalScopeNames, GlobalScopeNamesPlural}

Regenerate the live pull, then run this script.
"""

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "metadata" / "live-pull"
OUTDIR = ROOT / "field-reference" / "modules"
BASE = "https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1"
GEN_DATE = "2026-06-06"

ENUMY = {"enum", "multiEnum", "array", "checklist"}


def field_label(name, ent):
    return (ent.get("fields", {}) or {}).get(name) or (ent.get("links", {}) or {}).get(name) or name


def link_label(name, ent):
    return (ent.get("links", {}) or {}).get(name) or name


def fmt_default(d):
    if d is None or d == "":
        return "—"
    if isinstance(d, bool):
        return "true" if d else "false"
    if isinstance(d, (int, float)):
        return str(d)
    return f"`{d}`"


def field_constraints(fdef):
    parts = []
    if fdef.get("required"):
        parts.append("required")
    if fdef.get("readOnly"):
        parts.append("read-only")
    if fdef.get("notNull"):
        parts.append("not-null")
    if fdef.get("min") is not None:
        parts.append(f"min {fdef['min']}")
    if fdef.get("max") is not None:
        parts.append(f"max {fdef['max']}")
    elif fdef.get("maxLength") is not None:
        parts.append(f"max {fdef['maxLength']}")
    if fdef.get("pattern"):
        parts.append("pattern")
    if fdef.get("isCustom"):
        parts.append("custom")
    return ", ".join(parts) if parts else "—"


def link_notes(ldef):
    parts = []
    if ldef.get("readOnly"):
        parts.append("read-only")
    if ldef.get("isCustom"):
        parts.append("custom")
    if ldef.get("noJoin"):
        parts.append("no-join")
    if ldef.get("audited"):
        parts.append("audited")
    if ldef.get("relationName"):
        parts.append(f"relation `{ldef['relationName']}`")
    return ", ".join(parts) if parts else "—"


def entity_type(scope):
    scope = scope or {}
    if scope.get("isCustom"):
        return "Custom"
    return "Core"


def render_module(entity, edef, scope, ent_i18n, plural, label):
    fields = edef.get("fields", {})
    links = edef.get("links", {})
    is_custom = entity_type(scope) == "Custom"
    module = (scope or {}).get("module", "")
    type_str = f"{'Custom' if is_custom else 'Core'} entity"
    if module:
        type_str += f" (module: `{module}`)"

    out = []
    out.append(f"# {label}")
    out.append("")
    out.append(f"**Entity name:** `{entity}`  ")
    out.append(f"**Plural label:** {plural}  ")
    out.append(f"**Type:** {type_str}  ")
    out.append(f"**Field count:** {len(fields)}  ")
    out.append(f"**Link count:** {len(links)}  ")
    out.append("")
    out.append("**API endpoints**")
    out.append("")
    out.append(f"- List: `GET {BASE}/{entity}`")
    out.append(f"- Get:  `GET {BASE}/{entity}/{{id}}`")
    out.append(f"- Create: `POST {BASE}/{entity}`")
    out.append(f"- Update: `PATCH {BASE}/{entity}/{{id}}`")
    out.append(f"- Delete: `DELETE {BASE}/{entity}/{{id}}`")
    out.append("")

    # Fields table
    out.append("## Fields")
    out.append("")
    out.append("| API name | Label | Type | Required | Default | Constraints |")
    out.append("|---|---|---|---|---|---|")
    for name in sorted(fields):
        fdef = fields[name]
        out.append(
            f"| `{name}` | {field_label(name, ent_i18n)} | {fdef.get('type','')} | "
            f"{'yes' if fdef.get('required') else ''} | {fmt_default(fdef.get('default'))} | "
            f"{field_constraints(fdef)} |"
        )
    out.append("")

    # Allowed values
    enum_fields = [n for n in sorted(fields)
                   if fields[n].get("type") in ENUMY and fields[n].get("options")]
    if enum_fields:
        out.append("## Allowed values (enum / multi-enum / array / checklist)")
        out.append("")
        for name in enum_fields:
            fdef = fields[name]
            out.append(f"### `{name}` — {field_label(name, ent_i18n)}")
            out.append("")
            out.append(f"- Type: `{fdef.get('type','')}`")
            if fdef.get("default") not in (None, ""):
                out.append(f"- Default: {fmt_default(fdef.get('default'))}")
            out.append("- Options:")
            for opt in fdef.get("options", []):
                if opt == "":
                    out.append('  - `""` _(empty)_')
                else:
                    out.append(f"  - `{opt}`")
            out.append("")

    # Relationships
    if links:
        out.append("## Relationships (links)")
        out.append("")
        out.append("| API name | Label | Type | Target entity | Foreign link | Notes |")
        out.append("|---|---|---|---|---|---|")
        for name in sorted(links):
            ldef = links[name]
            target = ldef.get("entity") or ldef.get("foreign") or "—"
            foreign = ldef.get("foreign") or "—"
            out.append(
                f"| `{name}` | {link_label(name, ent_i18n)} | {ldef.get('type','')} | "
                f"`{target}` | `{foreign}` | {link_notes(ldef)} |"
            )
        out.append("")

    # Unique indexes
    indexes = edef.get("indexes", {})
    unique = {k: v for k, v in indexes.items() if v.get("unique")}
    if unique:
        out.append("## Unique indexes")
        out.append("")
        for name in sorted(unique):
            cols = ", ".join(f"`{c}`" for c in unique[name].get("columns", []))
            out.append(f"- **{name}**: {cols}")
        out.append("")

    out.append("---")
    out.append("")
    out.append(f"_Generated {GEN_DATE} from a read-only live metadata pull "
               f"(`metadata.php` cache, equivalent to `GET {BASE}/Metadata`)._")
    return "\n".join(out) + "\n"


def render_readme(entities, edefs, scopes, scope_names):
    out = []
    out.append("# EspoCRM Field Guide")
    out.append("")
    out.append("Per-module field reference for the RSG EspoCRM instance, generated from live `/Metadata`.")
    out.append("")
    out.append("## Instance")
    out.append("")
    out.append(f"- API base: `{BASE}`")
    out.append("- Authentication:")
    out.append("  - GET requests: header `X-Api-Key: {API_KEY}` (do NOT include `Content-Type`)")
    out.append("  - POST/PATCH/DELETE: header `Authorization: Basic <base64(API_KEY:)>` with `Content-Type: application/json`")
    out.append("")
    out.append("## Modules")
    out.append("")
    out.append("| Module | Label | Fields | Links | Type |")
    out.append("|---|---|---|---|---|")
    for e in sorted(entities):
        edef = edefs[e]
        scope = scopes.get(e) or {}
        label = scope_names.get(e, e)
        typ = entity_type(scope)
        if typ == "Custom" and scope.get("tab") is False:
            typ = "Custom (no nav tab)"
        out.append(f"| [{e}]({e}.md) | {label} | {len(edef.get('fields',{}))} | "
                   f"{len(edef.get('links',{}))} | {typ} |")
    out.append("")
    out.append("## How this was generated")
    out.append("")
    out.append("Source: a read-only live metadata pull (`metadata.php` cache, equivalent to "
               "`GET /api/v1/Metadata` keys `entityDefs`/`scopes`) plus the merged `en_US` i18n for labels. "
               "Regenerate with `tools/build-module-docs.py`.")
    out.append("")
    out.append(f"_Last generated: {GEN_DATE}_")
    return "\n".join(out) + "\n"


def main():
    src = json.loads((SRC / "docs_entitydefs.json").read_text())
    i18n = json.loads((SRC / "docs_i18n.json").read_text())
    edefs = src["entityDefs"]
    scopes = src["scopes"]
    ents = i18n["entities"]
    scope_names = i18n.get("GlobalScopeNames", {})
    scope_names_plural = i18n.get("GlobalScopeNamesPlural", {})

    entities = sorted(edefs.keys())
    for e in entities:
        plural = scope_names_plural.get(e) or scope_names.get(e) or e
        label = scope_names.get(e) or e
        text = render_module(e, edefs[e], scopes.get(e), ents.get(e) or {}, plural, label)
        (OUTDIR / f"{e}.md").write_text(text)
    (OUTDIR / "README.md").write_text(render_readme(entities, edefs, scopes, scope_names))
    print(f"Wrote {len(entities)} module docs + README.md to {OUTDIR}")


if __name__ == "__main__":
    main()
