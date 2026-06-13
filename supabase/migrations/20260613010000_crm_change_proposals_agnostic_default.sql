-- LLM-agnostic: the proposer is pluggable (Claude, Hermes, other). The staging
-- table must not bake in one agent. Neutralize the proposed_by default from
-- 'hermes' to 'agent'; callers still set their own label (e.g. 'claude').

ALTER TABLE public.crm_change_proposals
  ALTER COLUMN proposed_by SET DEFAULT 'agent';

COMMENT ON COLUMN public.crm_change_proposals.proposed_by IS
  'Label of the agent that proposed this change (claude | hermes | other). Free-text, not model-specific.';
