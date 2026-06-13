-- Step A.3 — commission engine alignment (security)
-- Enable RLS on commission_ledger. The table is empty and only the engine
-- (server-side, service-role key) and n8n write it; service_role BYPASSES RLS,
-- so enabling with no policy locks out anon/authenticated while keeping the
-- engine working. The browser never holds the service-role key.
--
-- NOTE: commission_rules RLS is intentionally NOT here — it has 216 live rows and
-- existing readers; confirm those connect via service_role before enabling, or
-- add an explicit read policy. Tracked separately.

ALTER TABLE public.commission_ledger ENABLE ROW LEVEL SECURITY;
