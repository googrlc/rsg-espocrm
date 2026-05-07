-- RSG sync control foundation (EspoCRM / NowCerts / n8n / Hermes)
-- Applied to project wibscqhkvpijzqbhjphg (rsg-infrastructure).
-- Migration: create_sync_control_foundation

-- 1) sync_runs — one row per workflow execution
CREATE TABLE public.sync_runs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  workflow_name text NOT NULL,
  source_system text NOT NULL,
  destination_system text NOT NULL,
  started_at timestamptz NOT NULL DEFAULT now(),
  finished_at timestamptz,
  status text NOT NULL DEFAULT 'running',
  records_processed integer NOT NULL DEFAULT 0,
  records_created integer NOT NULL DEFAULT 0,
  records_updated integer NOT NULL DEFAULT 0,
  records_skipped integer NOT NULL DEFAULT 0,
  records_failed integer NOT NULL DEFAULT 0,
  error_count integer NOT NULL DEFAULT 0,
  triggered_by text,
  notes text,
  created_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.sync_runs IS 'Tracks each n8n (or other) sync execution; Hermes reads for daily sync health.';

CREATE INDEX idx_sync_runs_workflow_started ON public.sync_runs (workflow_name, started_at DESC);
CREATE INDEX idx_sync_runs_status ON public.sync_runs (status, started_at DESC);

-- 2) sync_mappings — cross-system identity map
CREATE TABLE public.sync_mappings (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  object_type text NOT NULL,
  supabase_table text,
  supabase_id uuid,
  espocrm_entity_type text,
  espocrm_id text,
  nowcerts_entity_type text,
  nowcerts_id text,
  external_key text,
  source_system text NOT NULL,
  match_method text,
  match_confidence numeric(5,4),
  verified_by_human boolean NOT NULL DEFAULT false,
  verified_by text,
  verified_at timestamptz,
  active boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.sync_mappings IS 'Maps records across EspoCRM, NowCerts, Supabase; prevents duplicates and unsafe writes.';

CREATE INDEX idx_sync_mappings_object ON public.sync_mappings (object_type, active);
CREATE INDEX idx_sync_mappings_espocrm ON public.sync_mappings (espocrm_entity_type, espocrm_id) WHERE espocrm_id IS NOT NULL;
CREATE INDEX idx_sync_mappings_nowcerts ON public.sync_mappings (nowcerts_entity_type, nowcerts_id) WHERE nowcerts_id IS NOT NULL;
CREATE INDEX idx_sync_mappings_external ON public.sync_mappings (external_key) WHERE external_key IS NOT NULL;
CREATE UNIQUE INDEX uq_sync_mappings_active_key
  ON public.sync_mappings (object_type, coalesce(espocrm_entity_type, ''), coalesce(espocrm_id, ''), coalesce(nowcerts_entity_type, ''), coalesce(nowcerts_id, ''))
  WHERE active = true AND (espocrm_id IS NOT NULL OR nowcerts_id IS NOT NULL);

-- 3) sync_audit_log — every sync attempt
CREATE TABLE public.sync_audit_log (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  workflow_name text NOT NULL,
  run_id uuid REFERENCES public.sync_runs(id) ON DELETE SET NULL,
  object_type text NOT NULL,
  object_id text NOT NULL,
  source_system text NOT NULL,
  destination_system text NOT NULL,
  action text NOT NULL,
  status text NOT NULL,
  match_method text,
  match_confidence numeric(5,4),
  payload_hash text,
  request_payload jsonb,
  response_payload jsonb,
  before_snapshot jsonb,
  after_snapshot jsonb,
  error_message text,
  executed_by text,
  executed_at timestamptz NOT NULL DEFAULT now(),
  created_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.sync_audit_log IS 'Append-only log of sync attempts (success, failure, skip, conflict).';

CREATE INDEX idx_sync_audit_run ON public.sync_audit_log (run_id, created_at DESC);
CREATE INDEX idx_sync_audit_object ON public.sync_audit_log (object_type, object_id, created_at DESC);
CREATE INDEX idx_sync_audit_workflow ON public.sync_audit_log (workflow_name, created_at DESC);
CREATE INDEX idx_sync_audit_status ON public.sync_audit_log (status, created_at DESC);

-- 4) sync_errors — failed / unresolved sync events
CREATE TABLE public.sync_errors (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  workflow_name text NOT NULL,
  run_id uuid REFERENCES public.sync_runs(id) ON DELETE SET NULL,
  object_type text NOT NULL,
  object_id text NOT NULL,
  source_system text NOT NULL,
  destination_system text NOT NULL,
  error_type text NOT NULL,
  error_message text NOT NULL,
  payload jsonb,
  retry_count integer NOT NULL DEFAULT 0,
  max_retries integer NOT NULL DEFAULT 5,
  next_retry_at timestamptz,
  status text NOT NULL DEFAULT 'open',
  assigned_to text,
  resolved_by text,
  resolved_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.sync_errors IS 'Failed sync events and retries; n8n/Hermes triage.';

CREATE INDEX idx_sync_errors_status ON public.sync_errors (status, next_retry_at);
CREATE INDEX idx_sync_errors_workflow ON public.sync_errors (workflow_name, created_at DESC);
CREATE INDEX idx_sync_errors_object ON public.sync_errors (object_type, object_id);

-- 5) sync_conflicts — field-level disagreements for human review
CREATE TABLE public.sync_conflicts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  object_type text NOT NULL,
  supabase_id uuid,
  espocrm_id text,
  nowcerts_id text,
  field_name text NOT NULL,
  espocrm_value jsonb,
  nowcerts_value jsonb,
  recommended_value jsonb,
  source_of_truth text,
  confidence_score numeric(5,4),
  status text NOT NULL DEFAULT 'open',
  reviewed_by text,
  reviewed_at timestamptz,
  resolution text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.sync_conflicts IS 'EspoCRM vs NowCerts field conflicts pending human resolution.';

CREATE INDEX idx_sync_conflicts_status ON public.sync_conflicts (status, created_at DESC);
CREATE INDEX idx_sync_conflicts_object ON public.sync_conflicts (object_type, espocrm_id, nowcerts_id);

-- 6) inbound_sync_staging — raw inbound before normalization
CREATE TABLE public.inbound_sync_staging (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  source_system text NOT NULL,
  source_object_type text NOT NULL,
  source_object_id text NOT NULL,
  raw_payload jsonb NOT NULL,
  payload_hash text,
  received_at timestamptz NOT NULL DEFAULT now(),
  processed boolean NOT NULL DEFAULT false,
  processed_at timestamptz,
  processing_status text NOT NULL DEFAULT 'pending',
  error_message text
);

COMMENT ON TABLE public.inbound_sync_staging IS 'Raw inbound rows from EspoCRM/NowCerts before normalize + promote.';

CREATE INDEX idx_inbound_staging_unprocessed ON public.inbound_sync_staging (processed, received_at) WHERE NOT processed;
CREATE INDEX idx_inbound_staging_source ON public.inbound_sync_staging (source_system, source_object_type, received_at DESC);

-- 7) outbound_sync_queue — approved outbound work
CREATE TABLE public.outbound_sync_queue (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  object_type text NOT NULL,
  object_id text NOT NULL,
  destination_system text NOT NULL,
  action text NOT NULL,
  priority integer NOT NULL DEFAULT 100,
  payload jsonb NOT NULL,
  status text NOT NULL DEFAULT 'queued',
  scheduled_for timestamptz NOT NULL DEFAULT now(),
  attempts integer NOT NULL DEFAULT 0,
  last_attempt_at timestamptz,
  last_error text,
  created_by text,
  approved_by text,
  approved_at timestamptz,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

COMMENT ON TABLE public.outbound_sync_queue IS 'Approved outbound writes to EspoCRM or NowCerts via workers/n8n.';

CREATE INDEX idx_outbound_queue_due ON public.outbound_sync_queue (status, scheduled_for, priority);
CREATE INDEX idx_outbound_queue_dest ON public.outbound_sync_queue (destination_system, status);

-- updated_at maintenance
CREATE OR REPLACE FUNCTION public.set_updated_at()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  NEW.updated_at := now();
  RETURN NEW;
END;
$$;

CREATE TRIGGER tr_sync_mappings_updated_at
  BEFORE UPDATE ON public.sync_mappings
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_sync_errors_updated_at
  BEFORE UPDATE ON public.sync_errors
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_sync_conflicts_updated_at
  BEFORE UPDATE ON public.sync_conflicts
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();

CREATE TRIGGER tr_outbound_sync_queue_updated_at
  BEFORE UPDATE ON public.outbound_sync_queue
  FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
