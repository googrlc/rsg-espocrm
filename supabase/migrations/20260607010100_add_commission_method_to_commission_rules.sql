-- Step A.2 — commission engine alignment
-- Give the rate cards a calc-method column so the engine's non-%-of-premium
-- methods (PEO payroll, admin fee, per-employee, etc.) have a home. The 216
-- existing rows are nb_percent/renewal_percent based, so they default to
-- '% of Premium'.
--
-- Valid values mirror the engine CommissionMethod union:
--   '% of Premium' | '% of Payroll' | 'Flat $' | 'Per Employee'
--   | '% of Monthly Premium' | '% of Admin Fee' | 'Manual'

ALTER TABLE public.commission_rules
  ADD COLUMN IF NOT EXISTS commission_method text NOT NULL DEFAULT '% of Premium';

COMMENT ON COLUMN public.commission_rules.commission_method IS
  'Calc method (mirrors engine CommissionMethod). Existing rows default to % of Premium.';
