-- Step A.1 — commission engine alignment
-- Add EspoCRM Policy id to the ledger so the Policy->engine bridge can upsert by
-- it. The ledger's only Espo key today is espocrm_opportunity_id (wrong grain).
-- Complements nowcerts_policy_id / policy_number.

ALTER TABLE public.commission_ledger
  ADD COLUMN IF NOT EXISTS espocrm_policy_id text;

CREATE INDEX IF NOT EXISTS idx_commission_ledger_espocrm_policy
  ON public.commission_ledger (espocrm_policy_id)
  WHERE espocrm_policy_id IS NOT NULL;

COMMENT ON COLUMN public.commission_ledger.espocrm_policy_id IS
  'EspoCRM Policy id — bridge upsert key. Complements nowcerts_policy_id / policy_number.';
