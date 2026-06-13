# Commission Engine Bridge — Go-Live Checklist

Brings the AMS → EspoCRM → Supabase → commission engine flow online. Code + Step A
migrations are done; this is the wiring + deploy. See `COMMISSION-ENGINE-BRIDGE.md`
(contract) and `COMMISSION-ENGINE-SUPABASE-CROSSWALK.md` (model).

## 0. Security first
- [ ] **Rotate the Supabase `service_role` key** (Project Settings → API). The one
      used during testing was pasted into a chat transcript — treat as compromised.
- [ ] The new key lives only in the engine **host env** (Cloud Run / AI Studio
      secrets) and local `.env.local` (gitignored) — never committed.

## 1. Supabase (project `rsg-infrastructure`, `wibscqhkvpijzqbhjphg`)
- [x] `commission_ledger.espocrm_policy_id` + index (applied)
- [x] `commission_rules.commission_method` default `% of Premium` (applied)
- [x] RLS on `commission_ledger` (applied)
- [ ] **RLS on `commission_rules`** — recommended; no anon-key reader found in repos
      and `sync_*`/`crm_*` already use RLS. Apply only after confirming Hermes/n8n
      read via the service-role key:
      ```sql
      ALTER TABLE public.commission_rules ENABLE ROW LEVEL SECURITY;
      ```
- [ ] (Optional) dedupe truly-identical rate rows; leave real `tier_label` tiers.

## 2. Commission engine (Commsions repo)
- [ ] Deploy with host env set:
      - `SUPABASE_URL=https://wibscqhkvpijzqbhjphg.supabase.co`
      - `SUPABASE_SERVICE_KEY=<rotated service_role key>`
      - `COMMISSION_SYNC_SECRET=<shared secret>`  (matches EspoCRM, below)
- [ ] Start: `npm run start` (builds + serves on `PORT`, default 8080).
- [ ] Confirm the log reads `won-policy store: Supabase commission_ledger (...)`
      (not a file path) — proves the key loaded.
- [ ] `GET /api/health` → `{ok:true}`; `GET /api/rules` → `source:"commission_rules"`.

## 3. EspoCRM (rsg-espocrm)
- [ ] Deploy the two files: `custom/Espo/Custom/Classes/Policy/PolicyClosedWebhookDispatcher.php`,
      `custom/Espo/Custom/Hooks/Policy/SendPolicyClosedWebhook.php`.
- [ ] In `data/config.php` (or override):
      ```php
      'commissionEngineWebhookUrl' => 'https://<engine-host>/api/won-policies',
      'commissionEngineWebhookSecret' => '<same shared secret as engine>',
      // optional: 'commissionEngineClosedStatusList' => ['Active'],
      // optional: 'commissionEngineCancelStatusList' => ['Cancelled','Flat Cancel','Lapsed'],
      ```
- [ ] Clear cache / Admin → Rebuild.

## 4. Smoke test (end to end)
- [ ] Flip a test Policy to `status = Active` in EspoCRM (or bind one).
- [ ] Confirm a row appears:
      ```sql
      select espocrm_policy_id, carrier_name, lob, gross_premium,
             expected_commission, reconciliation_status, audit_status, statement_source
      from public.commission_ledger where statement_source = 'espo_bridge'
      order by created_at desc limit 5;
      ```
- [ ] In the engine UI → Won Policies, the policy shows with expected commission.
- [ ] Cancel the Policy (`Cancelled`/`Flat Cancel`/`Lapsed`) → its ledger row goes
      `reconciliation_status='voided'`, `expected_commission=0`; the engine shows it
      struck-through "Reversed".
- [ ] Check carrier/LOB names match `commission_rules` (mismatch → expected $0).
- [ ] Rows flagged `audit_status='tier_ambiguous'` = verify the tier/rate manually.

## 5. Notes
- Idempotent: re-binds / AMS corrections re-`POST`; the bridge upserts its one
  `statement_source='espo_bridge'` row per policy (`espocrm_policy_id`).
- Tiered/life rates (NEXT, SIMPLICITY, …) resolve exactly only once the bridge
  sends `tierLabel`; until then they pick a deterministic tier + flag ambiguous.
- Reconciliation/actuals (`actual_commission`, `delta`, `payment_received`) are a
  separate inflow (n8n/statements), not written by this bridge.
