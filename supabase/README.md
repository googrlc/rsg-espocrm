# Supabase DDL (RSG)

This folder holds **versioned SQL migrations** for the RSG Supabase project **rsg-infrastructure** (`wibscqhkvpijzqbhjphg`, `us-east-1`).

## Canonical schema repo

Table **JSON** snapshots (`supabase/{table}.json`), field crosswalks, and changelog live in **`googrlc/rsg-data-schema`**. When you have push access, copy new migrations there under `supabase/migrations/` and update `CHANGELOG.md` so agents and n8n stay aligned with one source of truth.

## Applying migrations

Use the [Supabase CLI](https://supabase.com/docs/guides/cli) against the linked project, or run the SQL in the dashboard SQL editor. Migration filenames use the Supabase timestamp convention (e.g. `20260507011837_*.sql`).

## Already applied

`20260507011837_create_sync_control_foundation.sql` was applied to the live database via the Supabase dashboard/MCP; the file here records the same DDL for git review and drift detection.
