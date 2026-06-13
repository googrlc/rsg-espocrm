-- Step A follow-up — commission engine alignment (security)
-- Enable RLS on commission_rules (216 live rate cards). service_role BYPASSES
-- RLS, so the engine (server-side service-role key) and service-role automation
-- keep full access; anon/authenticated are locked out. No anon-key reader was
-- found in the repos, and sibling tables (sync_*, crm_*) already run with RLS.
--
-- Reversible if any anon/authenticated consumer turns up:
--   ALTER TABLE public.commission_rules DISABLE ROW LEVEL SECURITY;

ALTER TABLE public.commission_rules ENABLE ROW LEVEL SECURITY;
