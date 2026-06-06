#!/usr/bin/env python3
"""DEPRECATED — superseded by tools/build-crm-field-inventory.py.

This generator reads metadata/full-metadata.json, which is a STALE camelCase
snapshot (e.g. `accountStatus`, 365 Account fields) that no longer matches the
live server (snake_case `account_status`, 279 Account fields). Its db_type column
is metadata-derived, not the real DB schema.

Use instead: exports/crm_fields_account_contact_policy.csv (built from a live
read-only pull with real DB column types). The only modules this old report still
covers that the new one does not yet: Lead, Opportunity, Renewal.

Original purpose: summarize field definitions for EspoCRM modules for reference.
Source: metadata/full-metadata.json plus _planned/metadata/entityDefs/Quote.json.
"""

import csv
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
META = ROOT / "metadata" / "full-metadata.json"
PLANNED_QUOTE = ROOT / "_planned" / "metadata" / "entityDefs" / "Quote.json"
OUT = ROOT / "field-reference" / "modules-field-schema.deprecated.csv"

ENTITIES = [
    "Account",
    "Contact",
    "Opportunity",
    "Task",
    "Quote",
    "Lead",
    "Policy",
    "Renewal",
    "Commission",
]

# Espo field type -> (API payload shape, suggested use case hint)
# These hints are deliberately short — refine for each project as needed.
TYPE_HINTS = {
    "varchar": ("string", "Short free-text identifier or label"),
    "text": ("string (multi-line)", "Notes, descriptions, multi-line content"),
    "url": ("string (URL)", "Hyperlink to external resource"),
    "email": ("string (email) or array via emailAddressData", "Email contact info"),
    "phone": ("string or array via phoneNumberData", "Phone contact info"),
    "int": ("integer", "Numeric counter / quantity"),
    "float": ("number", "Decimal numeric value"),
    "currency": ("object: {amount: number, currency: string}", "Monetary amount with currency code"),
    "currencyConverted": ("number (read-only, base currency)", "Converted-to-base-currency mirror"),
    "bool": ("boolean", "Flag / checkbox"),
    "date": ("string (YYYY-MM-DD)", "Calendar date"),
    "datetime": ("string (ISO 8601 UTC)", "Timestamp"),
    "datetimeOptional": ("string (date or datetime)", "Timestamp where time component is optional"),
    "enum": ("string (one of options)", "Pick-list / status"),
    "multiEnum": ("array of strings", "Multi-select tags"),
    "array": ("array of strings", "Free-form tag list"),
    "checklist": ("array of strings", "Checklist of items"),
    "link": ("Field+'Id' and Field+'Name' on payload", "FK to a single related record"),
    "linkParent": ("Field+'Id', Field+'Type', Field+'Name'", "Polymorphic FK (parent of many possible types)"),
    "linkMultiple": ("Field+'Ids' (array) + Field+'Names' (map)", "M:N relation"),
    "address": ("Street/City/State/PostalCode/Country sub-fields", "Postal address"),
    "personName": ("salutation/first/middle/last sub-fields", "Person's full name"),
    "image": ("attachment id + name", "Image attachment"),
    "file": ("attachment id + name", "File attachment"),
    "attachmentMultiple": ("Field+'Ids' (array)", "Multiple file attachments"),
    "autoincrement": ("integer (server-assigned)", "Sequential record number"),
    "duration": ("integer seconds (paired with dateStart/dateEnd)", "Time duration"),
    "rangeInt": ("from/to integer pair", "Integer range"),
    "rangeFloat": ("from/to number pair", "Numeric range"),
    "rangeCurrency": ("from/to currency pair", "Currency range"),
    "foreign": ("read-only mirror of related field", "Denormalized lookup from a linked entity"),
    "number": ("integer (auto, read-only)", "Auto-numbered display id"),
    "password": ("string (write-only)", "Credential storage"),
    "wysiwyg": ("string (HTML)", "Rich-text content"),
    "map": ("lat/lng pair", "Geographic point"),
    "barcode": ("string", "Encoded identifier"),
    "colorpicker": ("string (#rrggbb)", "Color selection"),
    "json": ("JSON object/array", "Free-form structured data"),
    "jsonObject": ("JSON object", "Structured object payload"),
    "jsonArray": ("JSON array", "Structured array payload"),
}

# Espo internal/system-managed field names common across entities.
SYSTEM_FIELDS = {
    "id", "deleted", "createdAt", "modifiedAt", "createdBy", "modifiedBy",
    "stream", "streamUpdatedAt", "versionNumber", "teams", "assignedUser",
    "collaborators", "followers", "isFollowed",
}


def load_entity_def(metadata: dict, entity: str) -> dict:
    if entity == "Quote":
        return json.loads(PLANNED_QUOTE.read_text())
    return metadata["entityDefs"][entity]


def api_payload_shape(field_name: str, field_def: dict) -> str:
    ftype = field_def.get("type", "")
    hint = TYPE_HINTS.get(ftype, ("see Espo docs", ""))[0]
    # Refine for link types so the actual JSON keys appear.
    if ftype == "link":
        return f"{field_name}Id (string), {field_name}Name (string, read-only mirror)"
    if ftype == "linkParent":
        return f"{field_name}Id, {field_name}Type, {field_name}Name"
    if ftype == "linkMultiple":
        return f"{field_name}Ids (array), {field_name}Names (object map)"
    if ftype == "address":
        return (
            f"{field_name}Street, {field_name}City, {field_name}State, "
            f"{field_name}PostalCode, {field_name}Country, {field_name}Map"
        )
    if ftype == "personName":
        return (
            f"{field_name}Salutation, {field_name}First, {field_name}Middle, "
            f"{field_name}Last (server also returns combined '{field_name}')"
        )
    if ftype == "currency":
        return f"{field_name} (number), {field_name}Currency (string ISO code)"
    if ftype == "email":
        return f"{field_name} (primary) + emailAddressData[] for multiple"
    if ftype == "phone":
        return f"{field_name} (primary) + phoneNumberData[] for multiple"
    if ftype == "duration":
        return f"{field_name} (seconds) paired with dateStart/dateEnd"
    return hint


def constraints(field_def: dict) -> str:
    parts = []
    if field_def.get("required"):
        parts.append("required")
    if field_def.get("readOnly"):
        parts.append("read-only")
    if field_def.get("notStorable"):
        parts.append("not stored (computed)")
    if "maxLength" in field_def:
        parts.append(f"maxLength={field_def['maxLength']}")
    if "min" in field_def:
        parts.append(f"min={field_def['min']}")
    if "max" in field_def:
        parts.append(f"max={field_def['max']}")
    if field_def.get("unique"):
        parts.append("unique")
    if field_def.get("audited"):
        parts.append("audited")
    if field_def.get("isPersonalData"):
        parts.append("PII")
    if field_def.get("isCustom"):
        parts.append("custom field")
    if "default" in field_def and field_def["default"] not in ("", None):
        parts.append(f"default={field_def['default']!r}")
    return "; ".join(parts)


def options_or_target(field_def: dict, links: dict, name: str) -> str:
    ftype = field_def.get("type", "")
    if ftype in ("enum", "multiEnum"):
        opts = [o for o in field_def.get("options", []) if o != ""]
        if len(opts) <= 12:
            return " | ".join(opts)
        return " | ".join(opts[:12]) + f" | ... ({len(opts)} total)"
    if ftype in ("link", "linkMultiple", "linkParent"):
        link = links.get(name, {})
        target = link.get("entity") or ",".join(link.get("entityList", []))
        ltype = link.get("type", "")
        if target:
            return f"{ltype} -> {target}"
        return ltype
    if ftype == "foreign":
        link = field_def.get("link")
        ref = field_def.get("field")
        if link and ref:
            return f"via link '{link}', field '{ref}'"
    return ""


def use_case(entity: str, name: str, field_def: dict) -> str:
    ftype = field_def.get("type", "")
    tooltip = field_def.get("tooltipText")
    if tooltip:
        return tooltip
    if name in SYSTEM_FIELDS:
        return "System-managed audit/ownership field"
    return TYPE_HINTS.get(ftype, ("", "Custom field — refer to layout/docs"))[1]


def main() -> None:
    metadata = json.loads(META.read_text())
    OUT.parent.mkdir(parents=True, exist_ok=True)
    rows = []
    for entity in ENTITIES:
        entity_def = load_entity_def(metadata, entity)
        fields = entity_def.get("fields", {})
        links = entity_def.get("links", {})
        source = "planned (not yet on live server)" if entity == "Quote" else "live metadata"
        for name, fdef in fields.items():
            rows.append({
                "module": entity,
                "source": source,
                "field_name": name,
                "espo_type": fdef.get("type", ""),
                "db_type": fdef.get("dbType", ""),
                "api_payload": api_payload_shape(name, fdef),
                "options_or_link_target": options_or_target(fdef, links, name),
                "constraints": constraints(fdef),
                "use_case": use_case(entity, name, fdef),
            })
    with OUT.open("w", newline="") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=[
                "module",
                "source",
                "field_name",
                "espo_type",
                "db_type",
                "api_payload",
                "options_or_link_target",
                "constraints",
                "use_case",
            ],
        )
        writer.writeheader()
        writer.writerows(rows)
    print(f"Wrote {len(rows)} rows to {OUT}")


if __name__ == "__main__":
    main()
