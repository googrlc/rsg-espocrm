-- CRM write-staging — the propose -> approve -> commit gate.
-- No agent (regardless of which LLM, or none) writes live EspoCRM records directly.
-- Agents write PROPOSALS here; a deterministic committer applies approved rows to
-- EspoCRM with correct per-entity casing + post-write read-back. Model-agnostic by
-- design: the proposing agent labels itself in proposed_by. See COMMISSION-WORKSPACE-DESIGN.md.
--
-- RLS is enabled with no policy: service_role BYPASSES RLS (the bridge/committer use
-- the service-role key server-side), while anon/authenticated are locked out. The
-- browser never holds the service-role key. Mirrors the commission_ledger pattern.

CREATE TABLE IF NOT EXISTS public.crm_change_proposals (
  id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  entity        text NOT NULL CHECK (entity IN ('Account', 'Contact', 'Policy', 'Opportunity')),
  match_key     text,                         -- e.g. momentum_client_id, for dedup before write
  espocrm_id    text,                         -- resolved target id; NULL = needs matching
  op            text NOT NULL DEFAULT 'upsert' CHECK (op IN ('upsert', 'enrich', 'update')),
  before        jsonb NOT NULL DEFAULT '{}'::jsonb,   -- snapshot at propose time
  after         jsonb NOT NULL DEFAULT '{}'::jsonb,   -- proposed field values
  rationale     text,
  confidence    numeric CHECK (confidence IS NULL OR (confidence >= 0 AND confidence <= 1)),
  source        text,                         -- enrichment source / origin
  status        text NOT NULL DEFAULT 'pending'
                  CHECK (status IN ('pending', 'approved', 'rejected', 'committed', 'failed')),
  proposed_by   text NOT NULL DEFAULT 'agent',
  reviewed_by   text,
  committed_at  timestamptz,
  result        jsonb,                         -- committer read-back: what actually stuck
  error         text,                          -- failure / silent-drop detail
  created_at    timestamptz NOT NULL DEFAULT now(),
  updated_at    timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_crm_change_proposals_status
  ON public.crm_change_proposals (status);

CREATE INDEX IF NOT EXISTS idx_crm_change_proposals_entity_match
  ON public.crm_change_proposals (entity, match_key)
  WHERE match_key IS NOT NULL;

COMMENT ON TABLE public.crm_change_proposals IS
  'Staging for agent-proposed EspoCRM writes. Any agent proposes; humans approve; a deterministic committer applies. Model-agnostic. service_role-only via RLS.';

COMMENT ON COLUMN public.crm_change_proposals.proposed_by IS
  'Label of the agent that proposed this change (e.g. claude | hermes | other). Free-text, not model-specific.';

ALTER TABLE public.crm_change_proposals ENABLE ROW LEVEL SECURITY;
