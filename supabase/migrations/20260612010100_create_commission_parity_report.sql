-- Commission parity-reconciliation output — the cutover gate.
-- Before the EspoCRM Commission module goes read-only, the platform must prove it
-- reproduces the CRM's existing numbers (or that every diff has a named cause).
-- One row per compared pair, grouped by run_id. Read-only on both sides; this table
-- is the scorecard. See COMMISSION-WORKSPACE-DESIGN.md "Parity-reconciliation check".
--
-- RLS enabled, no policy: service_role-only (engine/job write it server-side),
-- anon/authenticated locked out. Mirrors commission_ledger.

CREATE TABLE IF NOT EXISTS public.commission_parity_report (
  id                          uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  run_id                      uuid NOT NULL,                 -- groups one reconciliation run
  bucket                      text NOT NULL
                                CHECK (bucket IN ('MATCH', 'EXPLAINED_DIFF', 'MISMATCH',
                                                  'CRM_ONLY', 'PLATFORM_ONLY')),
  cause                       text                           -- carrier_unmatched | tier_ambiguous |
                                                             -- lob_unmapped | rule_updated | rounding |
                                                             -- manual_entry | endorsement_gap | NULL
                                CHECK (cause IS NULL OR cause IN (
                                  'carrier_unmatched', 'tier_ambiguous', 'lob_unmapped',
                                  'rule_updated', 'rounding', 'manual_entry', 'endorsement_gap')),
  match_key                   text,                          -- ledgerKey or composite key used to pair
  espocrm_commission_id       text,                          -- CRM Commission record id (CRM side)
  espocrm_policy_id           text,
  ledger_id                   uuid REFERENCES public.commission_ledger (id) ON DELETE SET NULL,
  -- headline numbers
  crm_estimated_commission    numeric,                       -- CRM Commission.estimatedCommission
  platform_expected_commission numeric,                      -- commission_ledger.expected_commission
  delta                       numeric,                       -- crm - platform
  -- inputs that explain a gap
  crm_written_premium         numeric,
  platform_gross_premium      numeric,
  carrier                     text,
  lob                         text,
  commission_type             text,                          -- New Business | Renewal | Endorsement
  details                     jsonb NOT NULL DEFAULT '{}'::jsonb,  -- full field-by-field comparison
  created_at                  timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_commission_parity_run
  ON public.commission_parity_report (run_id);

CREATE INDEX IF NOT EXISTS idx_commission_parity_bucket
  ON public.commission_parity_report (run_id, bucket);

COMMENT ON TABLE public.commission_parity_report IS
  'Per-pair parity between CRM Commission records and platform commission_ledger. Gate for decommissioning the CRM Commission module. service_role-only via RLS.';

ALTER TABLE public.commission_parity_report ENABLE ROW LEVEL SECURITY;
