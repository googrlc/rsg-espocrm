---
name: supabase-sync-analytics-layer
description: |
  RSG Supabase as control layer between EspoCRM, NowCerts, n8n, Hermes, and analytics.
  Use for sync control schema (mappings, audit, errors, conflicts, queues), CRM/AMS mirrors,
  unified analytics facts/dims, marketing intelligence, AI enrichment, data quality, and
  reporting views. Triggers on Supabase migrations, sync_mappings, Hermes reporting,
  cross-system identity, or "sync + analytics" design.
---

# Supabase sync and analytics layer (RSG)

## System architecture

RSG uses **EspoCRM** as the CRM, **NowCerts** as the AMS and policy ledger, **n8n** for orchestration, **Hermes** (and related agents) for coordination and revenue intelligence, and **Supabase (Postgres)** as the **neutral control and analytics backbone**.

- **EspoCRM** remains the system of record for CRM workflows and human-facing CRM data.
- **NowCerts** remains the system of record for AMS and policy ledger truth where applicable.
- **Supabase** owns cross-system identity, sync state, audit, errors, conflicts, queues, normalized analytics, reporting views, marketing structure, data quality signals, and AI-readable enrichment—not replacing CRM or AMS UIs.

Any agent designing **EspoCRM ↔ NowCerts** (or other system) **write** paths should assume **Supabase sync control** is in the loop unless explicitly scoped otherwise.

---

## Supabase sync and analytics layer

Supabase is the neutral data backbone between EspoCRM, NowCerts, n8n, Hermes, and analytics.

Supabase should **not** replace EspoCRM or NowCerts.

Supabase owns:

- Cross-system identity mapping
- Sync state
- Sync audit logs
- Sync errors
- Conflict queues
- Data quality scoring
- Normalized analytics tables
- Reporting views
- Marketing segments
- AI enrichment results
- Revenue intelligence inputs for Hermes

EspoCRM remains the CRM. NowCerts remains the AMS and policy ledger. Supabase acts as the **referee**, **historian**, **analytics layer**, and **AI-readable structure**.

**Rule:** No direct EspoCRM ↔ NowCerts write should happen without Supabase sync mapping and audit logging.

**Rule:** Before building any EspoCRM ↔ NowCerts sync, **inspect and update the Supabase sync control tables first**. No sync workflow is complete unless it writes to **`sync_mappings`**, **`sync_audit_log`**, and **`sync_errors`** when applicable.

---

## Required Supabase table groups

Organize the schema into these groups:

1. Sync control
2. CRM mirror tables
3. AMS mirror tables
4. Unified analytics tables
5. Marketing intelligence tables
6. AI enrichment tables
7. Reporting views
8. Data quality tables

---

## Sync control tables

Build these **first**. Without them, syncing is not operationally safe.

### `sync_mappings`

**Purpose:** Maps records across EspoCRM, NowCerts, Supabase, and other systems.

**Required columns:**

- `id`
- `object_type`
- `supabase_table`
- `supabase_id`
- `espocrm_entity_type`
- `espocrm_id`
- `nowcerts_entity_type`
- `nowcerts_id`
- `external_key`
- `source_system`
- `match_method`
- `match_confidence`
- `verified_by_human`
- `verified_by`
- `verified_at`
- `active`
- `created_at`
- `updated_at`

**Usage:** Prevents duplicate records and enables safe cross-system sync.

**Example `object_type` values:** `account`, `contact`, `opportunity`, `policy`, `renewal`, `service_request`, `carrier`, `producer`, `campaign`

---

### `sync_audit_log`

**Purpose:** Stores every sync attempt, successful or failed.

**Required columns:**

- `id`
- `workflow_name`
- `run_id`
- `object_type`
- `object_id`
- `source_system`
- `destination_system`
- `action`
- `status`
- `match_method`
- `match_confidence`
- `payload_hash`
- `request_payload`
- `response_payload`
- `before_snapshot`
- `after_snapshot`
- `error_message`
- `executed_by`
- `executed_at`
- `created_at`

**Example `action` values:** `create`, `update`, `skip`, `conflict`, `retry`, `delete_requested`, `manual_review`

**Example `status` values:** `success`, `failed`, `skipped`, `conflict`, `pending_review`, `retried`

---

### `sync_errors`

**Purpose:** Failed sync events and unresolved exceptions.

**Required columns:**

- `id`
- `workflow_name`
- `run_id`
- `object_type`
- `object_id`
- `source_system`
- `destination_system`
- `error_type`
- `error_message`
- `payload`
- `retry_count`
- `max_retries`
- `next_retry_at`
- `status`
- `assigned_to`
- `resolved_by`
- `resolved_at`
- `created_at`
- `updated_at`

**Example `error_type` values:** `missing_required_field`, `duplicate_match`, `api_timeout`, `api_auth_error`, `invalid_payload`, `conflict_detected`, `rate_limit`, `unknown_error`

**Example `status` values:** `open`, `retry_scheduled`, `resolved`, `ignored`, `failed_permanently`

---

### `sync_conflicts`

**Purpose:** Conflicts between EspoCRM and NowCerts that require human review.

**Required columns:**

- `id`
- `object_type`
- `supabase_id`
- `espocrm_id`
- `nowcerts_id`
- `field_name`
- `espocrm_value`
- `nowcerts_value`
- `recommended_value`
- `source_of_truth`
- `confidence_score`
- `status`
- `reviewed_by`
- `reviewed_at`
- `resolution`
- `created_at`
- `updated_at`

**Example `status` values:** `open`, `reviewed`, `resolved`, `ignored`

**Usage:** When both systems disagree on a meaningful field.

---

### `sync_runs`

**Purpose:** Tracks each n8n (or equivalent) sync execution.

**Required columns:**

- `id`
- `workflow_name`
- `source_system`
- `destination_system`
- `started_at`
- `finished_at`
- `status`
- `records_processed`
- `records_created`
- `records_updated`
- `records_skipped`
- `records_failed`
- `error_count`
- `triggered_by`
- `notes`
- `created_at`

**Usage:** Hermes should read this for daily sync health summaries.

---

### `outbound_sync_queue`

**Purpose:** Queues approved records waiting to be sent from Supabase/n8n to EspoCRM or NowCerts.

**Required columns:**

- `id`
- `object_type`
- `object_id`
- `destination_system`
- `action`
- `priority`
- `payload`
- `status`
- `scheduled_for`
- `attempts`
- `last_attempt_at`
- `last_error`
- `created_by`
- `approved_by`
- `approved_at`
- `created_at`
- `updated_at`

**Example `status` values:** `queued`, `processing`, `completed`, `failed`, `cancelled`, `human_review_required`

---

### `inbound_sync_staging`

**Purpose:** Temporarily stages raw inbound records from EspoCRM or NowCerts before normalization.

**Required columns:**

- `id`
- `source_system`
- `source_object_type`
- `source_object_id`
- `raw_payload`
- `payload_hash`
- `received_at`
- `processed`
- `processed_at`
- `processing_status`
- `error_message`

**Usage:** Safe imports, debugging, replay, and auditability.

---

## CRM mirror tables

Mirrors **cleaned** EspoCRM data into Supabase for sync, reporting, and matching.

### `crm_accounts`

**Purpose:** Normalized EspoCRM account data.

**Required columns:** `id`, `espocrm_account_id`, `nowcerts_insured_id`, `account_name`, `named_insured`, `dba`, `normalized_name`, `entity_type`, `fein`, `website`, `main_email`, `main_phone`, `mailing_address_line1`, `mailing_address_line2`, `mailing_city`, `mailing_state`, `mailing_zip`, `physical_address_line1`, `physical_address_line2`, `physical_city`, `physical_state`, `physical_zip`, `client_type`, `lifecycle_stage`, `client_status`, `industry`, `naics_code`, `sic_code`, `producer_id`, `csr_owner_id`, `risk_tier`, `revenue_tier`, `retention_risk`, `cross_sell_eligible`, `marketing_status`, `sync_status`, `last_synced_at`, `created_at`, `updated_at`

### `crm_contacts`

**Purpose:** Normalized EspoCRM contacts.

**Required columns:** `id`, `espocrm_contact_id`, `nowcerts_contact_id`, `account_id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `mobile_phone`, `role`, `title`, `is_primary`, `is_decision_maker`, `communication_preference`, `marketing_consent`, `do_not_contact`, `last_contacted_at`, `next_follow_up_at`, `sync_status`, `last_synced_at`, `created_at`, `updated_at`

### `crm_opportunities`

**Purpose:** EspoCRM pipeline data.

**Required columns:** `id`, `espocrm_opportunity_id`, `account_id`, `contact_id`, `opportunity_name`, `line_of_business`, `opportunity_type`, `stage`, `estimated_premium`, `estimated_revenue`, `probability`, `target_close_date`, `actual_close_date`, `producer_id`, `lead_source`, `campaign_id`, `submission_status`, `quote_status`, `proposal_sent_at`, `binding_requested_at`, `won_at`, `lost_at`, `lost_reason`, `competitor`, `next_step`, `next_follow_up_at`, `stale_flag`, `sync_status`, `created_at`, `updated_at`

### `crm_service_requests`

**Purpose:** Service requests visible in EspoCRM.

**Required columns:** `id`, `espocrm_service_request_id`, `nowcerts_task_id`, `account_id`, `contact_id`, `policy_id`, `request_name`, `request_type`, `status`, `priority`, `sla_due_at`, `assigned_owner_id`, `intake_channel`, `description`, `carrier_action_required`, `client_action_required`, `internal_action_required`, `pushed_to_nowcerts`, `pushed_to_nowcerts_at`, `resolution`, `closed_at`, `sync_status`, `created_at`, `updated_at`

---

## AMS mirror tables

Mirrors **NowCerts** into Supabase.

### `ams_insureds`

**Required columns:** `id`, `nowcerts_insured_id`, `espocrm_account_id`, `account_id`, `insured_name`, `normalized_name`, `dba`, `fein`, `email`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `zip`, `client_type`, `status`, `producer_name`, `csr_name`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

### `ams_contacts`

**Required columns:** `id`, `nowcerts_contact_id`, `nowcerts_insured_id`, `account_id`, `first_name`, `last_name`, `full_name`, `email`, `phone`, `role`, `is_primary`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

### `ams_policies`

**Required columns:** `id`, `nowcerts_policy_id`, `nowcerts_insured_id`, `espocrm_policy_id`, `account_id`, `policy_number`, `line_of_business`, `policy_type`, `carrier`, `mga_or_wholesaler`, `status`, `effective_date`, `expiration_date`, `annual_premium`, `commission_rate`, `estimated_commission`, `producer_name`, `csr_name`, `is_active`, `is_quote`, `is_renewal`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

### `ams_tasks`

**Required columns:** `id`, `nowcerts_task_id`, `nowcerts_insured_id`, `nowcerts_policy_id`, `account_id`, `task_type`, `subject`, `description`, `status`, `priority`, `assigned_to`, `due_at`, `completed_at`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

### `ams_claims`

**Required columns:** `id`, `nowcerts_claim_id`, `nowcerts_insured_id`, `nowcerts_policy_id`, `account_id`, `claim_number`, `carrier`, `line_of_business`, `date_of_loss`, `claim_status`, `claim_type`, `amount_paid`, `amount_reserved`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

### `ams_pending_cancellations`

**Required columns:** `id`, `nowcerts_cancellation_id`, `nowcerts_insured_id`, `nowcerts_policy_id`, `account_id`, `notice_type`, `reason`, `notice_date`, `effective_cancel_date`, `status`, `action_required`, `assigned_owner_id`, `source_payload`, `last_pulled_at`, `created_at`, `updated_at`

---

## Unified analytics tables

Agency reporting foundation.

### `dim_clients`

**Required columns:** `id`, `account_id`, `espocrm_account_id`, `nowcerts_insured_id`, `client_name`, `normalized_name`, `client_type`, `status`, `industry`, `naics_code`, `sic_code`, `state`, `producer_id`, `csr_owner_id`, `first_client_date`, `last_activity_at`, `total_active_premium`, `total_estimated_commission`, `policy_count`, `active_policy_count`, `monoline_flag`, `multi_policy_flag`, `high_value_flag`, `retention_risk`, `cross_sell_score`, `data_quality_score`, `created_at`, `updated_at`

### `fact_policies`

**Required columns:** `id`, `policy_id`, `account_id`, `espocrm_policy_id`, `nowcerts_policy_id`, `carrier_id`, `producer_id`, `csr_owner_id`, `line_of_business`, `policy_status`, `effective_date`, `expiration_date`, `annual_premium`, `commission_rate`, `estimated_commission`, `is_active`, `is_new_business`, `is_renewal`, `is_cancelled`, `days_until_expiration`, `renewal_bucket`, `created_at`, `updated_at`

### `fact_opportunities`

**Required columns:** `id`, `opportunity_id`, `account_id`, `contact_id`, `producer_id`, `campaign_id`, `line_of_business`, `opportunity_type`, `stage`, `estimated_premium`, `estimated_revenue`, `probability`, `weighted_revenue`, `lead_source`, `created_date`, `target_close_date`, `actual_close_date`, `won_flag`, `lost_flag`, `lost_reason`, `sales_cycle_days`, `stale_flag`, `no_next_action_flag`, `created_at`, `updated_at`

### `fact_service_requests`

**Required columns:** `id`, `service_request_id`, `account_id`, `policy_id`, `assigned_owner_id`, `request_type`, `status`, `priority`, `intake_channel`, `created_at_source`, `sla_due_at`, `closed_at`, `completion_hours`, `overdue_flag`, `waiting_on_client_flag`, `waiting_on_carrier_flag`, `pushed_to_nowcerts_flag`, `created_at`, `updated_at`

### `fact_campaign_performance`

**Required columns:** `id`, `campaign_id`, `campaign_name`, `campaign_type`, `segment_id`, `audience_count`, `sent_count`, `delivered_count`, `opened_count`, `clicked_count`, `replied_count`, `meetings_booked`, `opportunities_created`, `quotes_generated`, `policies_bound`, `premium_generated`, `commission_generated`, `unsubscribed_count`, `suppressed_count`, `start_date`, `end_date`, `created_at`, `updated_at`

### `fact_renewals`

**Required columns:** `id`, `renewal_id`, `policy_id`, `account_id`, `producer_id`, `csr_owner_id`, `expiration_date`, `days_until_expiration`, `renewal_bucket`, `renewal_stage`, `expiring_premium`, `target_premium`, `renewal_probability`, `premium_at_risk`, `retention_risk`, `remarketing_needed`, `proposal_sent_flag`, `bound_flag`, `lost_flag`, `non_renewed_flag`, `next_action`, `next_action_date`, `created_at`, `updated_at`

---

## Marketing intelligence tables

### `marketing_segments`

**Required columns:** `id`, `segment_name`, `segment_type`, `description`, `criteria_json`, `source_view`, `active`, `owner_id`, `created_at`, `updated_at`

**Example `segment_type` values:** `cross_sell`, `renewal`, `win_back`, `referral`, `contractor`, `personal_lines`, `benefits`, `life`, `dormant`

### `marketing_segment_members`

**Required columns:** `id`, `segment_id`, `account_id`, `contact_id`, `included_reason`, `suppression_reason`, `eligible`, `snapshot_date`, `created_at`

### `marketing_campaigns`

**Required columns:** `id`, `espocrm_campaign_id`, `campaign_name`, `campaign_type`, `goal`, `target_segment_id`, `line_of_business`, `offer`, `message_angle`, `channel`, `status`, `start_date`, `end_date`, `owner_id`, `success_metric`, `created_at`, `updated_at`

### `marketing_touchpoints`

**Required columns:** `id`, `campaign_id`, `account_id`, `contact_id`, `touch_type`, `channel`, `subject`, `sent_at`, `opened_at`, `clicked_at`, `replied_at`, `meeting_booked_at`, `opportunity_created_id`, `outcome`, `created_at`

### `suppression_list`

**Required columns:** `id`, `account_id`, `contact_id`, `suppression_type`, `reason`, `source_system`, `start_date`, `end_date`, `active`, `created_at`, `updated_at`

**Example `suppression_type` values:** `do_not_contact`, `unsubscribed`, `invalid_email`, `invalid_phone`, `open_complaint`, `active_claim`, `cancellation_pending`, `duplicate_contact`, `missing_consent`

---

## AI enrichment tables

Hermes / agents reason here **without** overwriting official CRM/AMS records unless explicitly approved.

### `ai_enrichment_results`

**Required columns:** `id`, `object_type`, `object_id`, `enrichment_type`, `source_system`, `source_text`, `source_url`, `extracted_fields`, `recommendation`, `confidence_score`, `model_used`, `prompt_version`, `reviewed_by_human`, `approved_for_write`, `reviewed_by`, `reviewed_at`, `created_at`

### `ai_recommendations`

**Required columns:** `id`, `recommendation_type`, `account_id`, `policy_id`, `opportunity_id`, `service_request_id`, `campaign_id`, `title`, `recommendation`, `reason`, `estimated_premium_impact`, `estimated_commission_impact`, `priority`, `confidence_score`, `status`, `assigned_to`, `acted_on_at`, `dismissed_at`, `created_at`, `updated_at`

**Example `recommendation_type` values:** `cross_sell`, `renewal_risk`, `stale_opportunity`, `service_sla`, `marketing_campaign`, `data_quality`, `producer_follow_up`

### `embedding_chunks`

**Required columns:** `id`, `source_system`, `source_object_type`, `source_object_id`, `document_id`, `chunk_text`, `chunk_summary`, `metadata`, `embedding`, `created_at`, `updated_at`

**Usage:** Only if Supabase **pgvector** (or equivalent) is part of the architecture. Type `embedding` as `vector(...)` in migrations when enabled.

---

## Data quality tables

### `data_quality_issues`

**Required columns:** `id`, `object_type`, `object_id`, `issue_type`, `severity`, `field_name`, `current_value`, `recommended_value`, `source_system`, `status`, `assigned_to`, `resolved_by`, `resolved_at`, `created_at`, `updated_at`

**Example `issue_type` values:** `duplicate_record`, `missing_required_field`, `invalid_email`, `invalid_phone`, `missing_owner`, `missing_policy_number`, `missing_expiration_date`, `unmatched_record`, `sync_conflict`

### `duplicate_candidates`

**Required columns:** `id`, `object_type`, `primary_object_id`, `duplicate_object_id`, `primary_source_system`, `duplicate_source_system`, `match_score`, `match_reasons`, `status`, `reviewed_by`, `reviewed_at`, `resolution`, `created_at`, `updated_at`

---

## Required reporting views

Build or maintain these when analytics are requested.

### Executive views

- `vw_executive_scorecard`
- `vw_premium_progress_to_goal`
- `vw_revenue_by_month`
- `vw_revenue_by_quarter`
- `vw_premium_by_producer`
- `vw_premium_by_carrier`
- `vw_premium_by_line_of_business`
- `vw_book_of_business_summary`

### Sales views

- `vw_pipeline_summary`
- `vw_pipeline_by_stage`
- `vw_pipeline_by_producer`
- `vw_stale_opportunities`
- `vw_opportunities_without_next_action`
- `vw_win_loss_summary`
- `vw_lead_source_roi`

### Renewal views

- `vw_renewals_120_90_60_30_15`
- `vw_renewal_premium_at_risk`
- `vw_renewals_missing_action`
- `vw_renewals_needing_remarketing`
- `vw_non_renewals`
- `vw_pending_cancellations`

### Marketing views

- `vw_cross_sell_opportunities`
- `vw_monoline_accounts`
- `vw_campaign_performance`
- `vw_marketing_segment_members`
- `vw_life_insurance_candidates`
- `vw_benefits_candidates`
- `vw_contractor_campaign_targets`
- `vw_referral_candidates`
- `vw_winback_candidates`

### Service views

- `vw_open_service_requests`
- `vw_overdue_service_requests`
- `vw_service_requests_by_owner`
- `vw_service_requests_by_type`
- `vw_service_sla_performance`

### Sync and data quality views

- `vw_sync_health`
- `vw_failed_syncs`
- `vw_open_sync_conflicts`
- `vw_duplicate_candidates`
- `vw_missing_required_fields`
- `vw_unmatched_espocrm_records`
- `vw_unmatched_nowcerts_records`

---

## Default output formats

### For Supabase sync and analytics schema design

Return:

1. Business purpose  
2. Tables required  
3. Columns required  
4. Primary keys  
5. Foreign keys  
6. Unique constraints  
7. Indexes  
8. Sync status fields  
9. Audit fields  
10. Analytics fields  
11. Marketing fields  
12. AI enrichment fields  
13. Reporting views  
14. Data quality checks  
15. Migration order  
16. Test data examples  
17. n8n workflow impact  
18. Hermes reporting impact  

---

## Supabase build order

Do **not** build dashboards before sync control. Suggested migration order:

1. `sync_runs`
2. `sync_mappings`
3. `sync_audit_log`
4. `sync_errors`
5. `sync_conflicts`
6. `inbound_sync_staging`
7. `outbound_sync_queue`
8. `crm_accounts`
9. `crm_contacts`
10. `crm_opportunities`
11. `crm_service_requests`
12. `ams_insureds`
13. `ams_contacts`
14. `ams_policies`
15. `ams_tasks`
16. `ams_claims`
17. `ams_pending_cancellations`
18. `dim_clients`
19. `fact_policies`
20. `fact_opportunities`
21. `fact_renewals`
22. `fact_service_requests`
23. `marketing_segments`
24. `marketing_segment_members`
25. `marketing_campaigns`
26. `marketing_touchpoints`
27. `suppression_list`
28. `ai_enrichment_results`
29. `ai_recommendations`
30. `data_quality_issues`
31. `duplicate_candidates`
32. `fact_campaign_performance` (after facts that feed it, if applicable)
33. Reporting views (after underlying tables exist)

---

## Practical folder layout (logical grouping)

```text
Supabase
├── sync control
│   ├── sync_mappings
│   ├── sync_runs
│   ├── sync_audit_log
│   ├── sync_errors
│   ├── sync_conflicts
│   ├── inbound_sync_staging
│   └── outbound_sync_queue
├── EspoCRM mirror
│   ├── crm_accounts
│   ├── crm_contacts
│   ├── crm_opportunities
│   └── crm_service_requests
├── NowCerts mirror
│   ├── ams_insureds
│   ├── ams_contacts
│   ├── ams_policies
│   ├── ams_tasks
│   ├── ams_claims
│   └── ams_pending_cancellations
├── analytics
│   ├── dim_clients
│   ├── fact_policies
│   ├── fact_opportunities
│   ├── fact_renewals
│   ├── fact_service_requests
│   └── fact_campaign_performance
├── marketing
│   ├── marketing_segments
│   ├── marketing_segment_members
│   ├── marketing_campaigns
│   ├── marketing_touchpoints
│   └── suppression_list
├── AI intelligence
│   ├── ai_enrichment_results
│   ├── ai_recommendations
│   └── embedding_chunks
└── data quality
    ├── data_quality_issues
    └── duplicate_candidates
```

---

## Using this skill with MCP / migrations

When applying schema in Supabase:

- Prefer **migrations** (SQL or Supabase CLI) over ad-hoc dashboard edits for anything sync-critical.
- Add **RLS policies** and **service roles** explicitly; do not leave mirror tables world-writable.
- Keep **PII** out of `sync_audit_log.request_payload` / `response_payload` where possible; prefer hashes and redacted copies for high-sensitivity fields.

For repository work that only touches EspoCRM config, still use this skill when the task explicitly spans **Supabase + CRM + AMS sync design**.
