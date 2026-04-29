#!/usr/bin/env python3
"""Pass 4: rename Account entityDefs field keys, fix nested attribute refs, collection sortBy."""

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

# Pass 4 renames + companion fields written with nextRenewal/nextX
RENAME = {
    "employeeCount": "employee_count",
    "yearsInBusiness": "years_in_business",
    "annualRevenue": "annual_revenue",
    "estimatedRevenue": "estimated_revenue",
    "estimatedPremium": "estimated_premium",
    "annualPremium": "annual_premium",
    "totalActivePremium": "total_active_premium",
    "totalCarrierPremium": "total_carrier_premium",
    "linkedinUrl": "linkedin_url",
    "referralSource": "referral_source",
    "referralName": "referral_name",
    "renewalDate": "renewal_date",
    "nextRenewalDate": "next_renewal_date",
    "nextRenewalLob": "next_renewal_lob",
    "nextRenewalCarrier": "next_renewal_carrier",
    "renewalDecision": "renewal_decision",
    "renewalDecisionNotes": "renewal_decision_notes",
    "renewalOutreachStage": "renewal_outreach_stage",
    "renewalQuoteAmount": "renewal_quote_amount",
    "renewalQuoteCarrier": "renewal_quote_carrier",
    "renewalQuoteDate": "renewal_quote_date",
    "renewalQuoteReceived": "renewal_quote_received",
    "retentionRisk": "retention_risk",
    "lastContactDate": "last_contact_date",
    "lastContactMethod": "last_contact_method",
    "lastContactOutcome": "last_contact_outcome",
    "lastContactType": "last_contact_type",
    "momentumClientId": "momentum_client_id",
    "momentumLastSynced": "momentum_last_synced",
    "xDate": "x_date",
    "nextXDate": "next_x_date",
    "nextXDateLob": "next_x_date_lob",
    "clientSince": "client_since",
    "bbbRating": "bbb_rating",
    "sicCode": "sic_code",
    "agentOfAgencyCode": "agent_of_agency_code",
    "agentOfRecordDate": "agent_of_record_date",
    "carrierCode": "carrier_code",
    "googleDriveFolderUrl": "google_drive_folder_url",
    "npsScore": "nps_score",
    "npsDate": "nps_date",
    "riskScore": "risk_score",
    "coverageGaps": "coverage_gaps",
    "doNotContact": "do_not_contact",
    "preferredContact": "preferred_contact",
    "bestTimeToCall": "best_time_to_call",
    "outreachAttemptsCurrent": "outreach_attempts_current",
    "communicationNotes": "communication_notes",
    "driverCount": "driver_count",
    "vehicleCount": "vehicle_count",
    "propertyCount": "property_count",
    "propertyAddress": "property_address",
    "propertyCity": "property_city",
    "propertyState": "property_state",
    "propertyZip": "property_zip",
    "mvrFlag": "mvr_flag",
    "youthfulDriverFlag": "youthful_driver_flag",
    "rideshareDriverFlag": "rideshare_driver_flag",
    "rentalPropertyFlag": "rental_property_flag",
    "rentalPropertyCount": "rental_property_count",
    "rentalPropertyNotes": "rental_property_notes",
    "rateIncreaseFlag": "rate_increase_flag",
    "premiumChangeAmount": "premium_change_amount",
    "premiumChangePct": "premium_change_pct",
    "claimsCount3yr": "claims_count_3yr",
    "claimsCountLifetime": "claims_count_lifetime",
    "claimsOpen": "claims_open",
    "claimsNotes": "claims_notes",
    "lastClaimDate": "last_claim_date",
    "lastClaimLob": "last_claim_lob",
    "lastClaimStatus": "last_claim_status",
    "lastClaimType": "last_claim_type",
    "accountScore": "account_score",
    "accountStatus": "account_status",
    "accountType": "account_type",
    "scoreTier": "score_tier",
    "scoreTotal": "score_total",
    "scoreBreakdown": "score_breakdown",
    "scoreBundleDepth": "score_bundle_depth",
    "scoreChangeAmount": "score_change_amount",
    "scoreChangeDirection": "score_change_direction",
    "scoreClaimsActivity": "score_claims_activity",
    "scoreLastCalculated": "score_last_calculated",
    "scoreLastContact": "score_last_contact",
    "scorePaymentHistory": "score_payment_history",
    "scoreYearsRetained": "score_years_retained",
    "scoreAlertSent": "score_alert_sent",
    "daysToRenewal": "days_to_renewal",
    "keyFindings": "key_findings",
    "aiAssessment": "ai_assessment",
    "assessmentDate": "assessment_date",
    "intelAiSummary": "intel_ai_summary",
    "intelAnnualRevenueEst": "intel_annual_revenue_est",
    "intelBbbAccredited": "intel_bbb_accredited",
    "intelBbbComplaints": "intel_bbb_complaints",
    "intelBbbNotes": "intel_bbb_notes",
    "intelBbbRating": "intel_bbb_rating",
    "intelCargoType": "intel_cargo_type",
    "intelConfidence": "intel_confidence",
    "intelCrossSell": "intel_cross_sell",
    "intelDba": "intel_dba",
    "intelDotIncidents": "intel_dot_incidents",
    "intelEmployeeCount": "intel_employee_count",
    "intelEntityType": "intel_entity_type",
    "intelFleetSize": "intel_fleet_size",
    "intelGrowthIndicator": "intel_growth_indicator",
    "intelLegalName": "intel_legal_name",
    "intelLinkedinNotes": "intel_linkedin_notes",
    "intelLinkedinUrl": "intel_linkedin_url",
    "intelNaics": "intel_naics",
    "intelNewsNotes": "intel_news_notes",
    "intelOperatingRadius": "intel_operating_radius",
    "intelOshaViolations": "intel_osha_violations",
    "intelOwnerOperators": "intel_owner_operators",
    "intelPackLastRun": "intel_pack_last_run",
    "intelPackRun": "intel_pack_run",
    "intelPainPoints": "intel_pain_points",
    "intelRun": "intel_run",
    "intelRunBy": "intel_run_by",
    "intelRunDate": "intel_run_date",
    "intelSic": "intel_sic",
    "intelSignalLinkedin": "intel_signal_linkedin",
    "intelSignalNews": "intel_signal_news",
    "intelSources": "intel_sources",
    "intelSourcesHit": "intel_sources_hit",
    "intelUnderwritingFlag": "intel_underwriting_flag",
    "intelWebsite": "intel_website",
    "intelWebsiteNotes": "intel_website_notes",
    "intelYearsInBusiness": "intel_years_in_business",
    "insightObjection": "insight_objection",
    "insightOpener": "insight_opener",
    "insightRelationship": "insight_relationship",
    "insightSignal": "insight_signal",
}

MERGE_LAYOUT_HIDE = {
    "numberOfEmployees": {"layoutDetailDisabled": True, "layoutListDisabled": True},
    "totalAnnualPremium": {"layoutDetailDisabled": True, "layoutListDisabled": True},
    "websiteUrl": {"layoutDetailDisabled": True, "layoutListDisabled": True},
    # Duplicate of policyCountActive (both written in PolicyAccountSync); hide legacy counter from UI
    "activePolicyCount": {"layoutDetailDisabled": True, "layoutListDisabled": True},
}


def fix_attributes(obj):
    if isinstance(obj, dict):
        for k, v in list(obj.items()):
            if k == "attribute" and isinstance(v, str) and v in RENAME:
                obj[k] = RENAME[v]
            else:
                fix_attributes(v)
    elif isinstance(obj, list):
        for item in obj:
            fix_attributes(item)


def process_entity_defs():
    path = ROOT / "custom/Espo/Custom/Resources/metadata/entityDefs/Account.json"
    data = json.loads(path.read_text())
    old_fields = data["fields"]
    new_fields = {}
    for k, v in old_fields.items():
        nk = RENAME.get(k, k)
        new_fields[nk] = v
    for fk, flags in MERGE_LAYOUT_HIDE.items():
        if fk in new_fields:
            new_fields[fk] = {**new_fields[fk], **flags}
    data["fields"] = new_fields
    fix_attributes(data)
    col = data.get("collection", {})
    if "sortByList" in col:
        col["sortByList"] = [RENAME.get(x, x) for x in col["sortByList"]]
    path.write_text(json.dumps(data, indent=2) + "\n")
    print("Wrote", path)


def rename_i18n_keys(obj):
    """Rename dict keys wherever they match RENAME (shallow + nested fields/options)."""
    if isinstance(obj, dict):
        out = {}
        for k, v in obj.items():
            nk = RENAME.get(k, k)
            if isinstance(v, dict):
                out[nk] = rename_i18n_keys(v)
            else:
                out[nk] = v
        return out
    if isinstance(obj, list):
        return [rename_i18n_keys(x) for x in obj]
    return obj


def process_i18n():
    path = ROOT / "custom/Espo/Custom/Resources/i18n/en_US/Account.json"
    data = json.loads(path.read_text())
    if "fields" in data:
        data["fields"] = rename_i18n_keys(data["fields"])
    if "options" in data:
        data["options"] = rename_i18n_keys(data["options"])
    # [OLD] labels for merge fields (keys unchanged)
    f = data.setdefault("fields", {})
    if "numberOfEmployees" in f:
        f["numberOfEmployees"] = "[OLD] Number of Employees"
    if "totalAnnualPremium" in f:
        f["totalAnnualPremium"] = "[OLD] Total Annual Premium"
    if "websiteUrl" in f:
        f["websiteUrl"] = "[OLD] Website URL"
    if "activePolicyCount" in f:
        f["activePolicyCount"] = "[OLD] Active Policy Count"
    path.write_text(json.dumps(data, indent=2) + "\n")
    print("Wrote", path)


def scrub_layout_file(path: Path):
    text = path.read_text()
    for old, new in sorted(RENAME.items(), key=lambda x: -len(x[0])):
        text = text.replace(f'"name": "{old}"', f'"name": "{new}"')
        text = text.replace(f'"attribute": "{old}"', f'"attribute": "{new}"')
    path.write_text(text)


def process_layouts():
    base = ROOT / "custom/Espo/Custom/Resources/layouts/Account"
    for p in base.rglob("*.json"):
        scrub_layout_file(p)
        print("layout", p.relative_to(ROOT))


if __name__ == "__main__":
    process_entity_defs()
    process_i18n()
    process_layouts()
