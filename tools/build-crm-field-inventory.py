#!/usr/bin/env python3
"""Build the per-field CRM inventory CSV (exports/crm_fields_*.csv).

Reproduces the exact column format of the original Account/Contact/Policy
inventory and extends it to additional modules. Source data is pulled
read-only from the LIVE EspoCRM server (the field names are snake_case and the
DB column types are real, neither of which exists in the committed metadata):

  metadata.php  -> entityDefs.json  (merged entityDefs per entity)
  languages/en_US.php -> i18n.json   (merged i18n labels per entity)
  information_schema.columns -> dbcols.tsv  (TABLE\\tCOLUMN\\tCOLUMN_TYPE)

See the loader paths below. Run with the three pulled files present.
"""

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SRC = ROOT / "metadata" / "live-pull"
OUT = ROOT / "exports" / "crm_fields_account_contact_policy.csv"

# Module order in the report (alphabetical).
MODULES = ["Account", "Commission", "Contact", "Policy", "Quote", "Task"]

HEADER = [
    "Module", "Field Name", "Field Type", "Label", "Label Source",
    "Required", "Read Only", "Custom", "Linked Entity",
    "DB Table", "DB Column(s)", "DB Column Type(s)", "Options / Definition Details",
]

# Field types that never have their own DB column.
NOT_STORED_TYPES = {
    "linkMultiple", "currencyConverted", "foreign", "map", "email", "phone",
    "personName", "attachmentMultiple", "linkOne",
}

LINK_TYPES = {"link", "linkOne", "linkMultiple", "linkParent"}


def c2s(name: str) -> str:
    """camelCase / snake_case field name -> DB column base (EspoCRM convention)."""
    return re.sub(r"(?<!^)(?=[A-Z])", "_", name).lower()


def auto_label(name: str) -> str:
    spaced = re.sub(r"(?<!^)(?=[A-Z])", " ", name).replace("_", " ")
    words = [w for w in spaced.split() if w]
    return " ".join(w[0].upper() + w[1:] for w in words)


def table_name(entity: str) -> str:
    return c2s(entity)


def db_columns(name: str, fdef: dict) -> list[str]:
    """Ordered DB column names for a stored field, or [] if not stored."""
    ftype = fdef.get("type", "")
    if fdef.get("notStorable") or ftype in NOT_STORED_TYPES:
        return []
    base = c2s(name)
    if ftype == "currency":
        return [base, base + "_currency"]
    if ftype == "address":
        return [base + s for s in ("_street", "_city", "_state", "_country", "_postal_code")]
    if ftype == "link":
        return [base + "_id"]
    if ftype == "linkParent":
        return [base + "_id", base + "_type"]
    if ftype == "datetimeOptional":
        return [base, base + "_date"]
    return [base]


def linked_entity(name: str, fdef: dict, links: dict) -> str:
    ftype = fdef.get("type", "")
    if ftype not in LINK_TYPES:
        return ""
    link = links.get(name, {})
    if ftype == "linkParent":
        entity_list = fdef.get("entityList") or link.get("entityList", [])
        return ",".join(entity_list)
    return link.get("entity", "") or ""


def details(fdef: dict) -> str:
    parts = []
    ftype = fdef.get("type", "")
    if ftype in ("enum", "multiEnum"):
        opts = fdef.get("options", []) or []
        if opts:
            parts.append("options=[" + "|".join(str(o) for o in opts) + "]")
    if "maxLength" in fdef:
        parts.append(f"maxLength={fdef['maxLength']}")
    if "min" in fdef and fdef["min"] is not None:
        parts.append(f"min={fdef['min']}")
    if "max" in fdef and fdef["max"] is not None:
        parts.append(f"max={fdef['max']}")
    if "default" in fdef and not (fdef["default"] is None or fdef["default"] is False or fdef["default"] == ""):
        d = fdef["default"]
        if d is True:
            d = "true"
        parts.append(f"default={d}")
    if fdef.get("view"):
        parts.append(f"view={fdef['view']}")
    if fdef.get("pattern"):
        parts.append(f"pattern={fdef['pattern']}")
    if fdef.get("audited"):
        parts.append("audited=true")
    if fdef.get("notStorable"):
        parts.append("notStorable=true")
    return "; ".join(parts)


def label_and_source(name: str, i18n_entity: dict, glob: dict) -> tuple[str, str]:
    # Precedence: entity.fields -> entity.links -> Global.fields -> Global.links -> auto
    ent = i18n_entity or {}
    sources = [
        (ent.get("fields", {}) or {}, "i18n:fields"),
        (ent.get("links", {}) or {}, "i18n:links"),
        (glob.get("fields", {}) or {}, "i18n:fields"),
        (glob.get("links", {}) or {}, "i18n:links"),
    ]
    for table, src in sources:
        if table.get(name):
            return table[name], src
    return auto_label(name), "auto"


def csv_field(value: str) -> str:
    s = "" if value is None else str(value)
    if any(c in s for c in (' ', ',', '"', '\n', '\r')):
        return '"' + s.replace('"', '""') + '"'
    return s


def main() -> None:
    ed = json.loads((SRC / "entityDefs.json").read_text())
    i18n = json.loads((SRC / "i18n.json").read_text())
    glob = json.loads((SRC / "global_i18n.json").read_text())

    # DB columns: {table: {column: column_type}}
    dbcols: dict[str, dict[str, str]] = {}
    for line in (SRC / "dbcols.tsv").read_text().splitlines():
        parts = line.split("\t")
        if len(parts) == 3:
            dbcols.setdefault(parts[0], {})[parts[1]] = parts[2]

    rows = [HEADER]
    missing = []
    for entity in MODULES:
        edef = ed.get(entity) or {}
        fields = edef.get("fields", {})
        links = edef.get("links", {})
        tbl = table_name(entity)
        coltypes = dbcols.get(tbl, {})
        for name in sorted(fields.keys()):
            fdef = fields[name]
            cols = db_columns(name, fdef)
            if cols:
                types = []
                for c in cols:
                    if c not in coltypes:
                        missing.append(f"{entity}.{name} -> {tbl}.{c}")
                    types.append(coltypes.get(c, ""))
                db_col_str = "|".join(cols)
                db_type_str = "|".join(types)
            else:
                db_col_str = "(not stored)"
                db_type_str = ""
            label, src = label_and_source(name, i18n.get(entity), glob)
            rows.append([
                entity,
                name,
                fdef.get("type", ""),
                label,
                src,
                "yes" if fdef.get("required") else "",
                "yes" if fdef.get("readOnly") else "",
                "yes" if fdef.get("isCustom") else "",
                linked_entity(name, fdef, links),
                tbl,
                db_col_str,
                db_type_str,
                details(fdef),
            ])

    text = "\n".join(",".join(csv_field(c) for c in row) for row in rows) + "\n"
    OUT.write_text(text)
    print(f"Wrote {len(rows) - 1} field rows across {len(MODULES)} modules to {OUT}")
    if missing:
        print(f"\nWARNING: {len(missing)} computed columns not found in DB:")
        for m in missing[:40]:
            print("  " + m)


if __name__ == "__main__":
    main()
