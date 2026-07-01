-- =====================================================================================
-- Renewal Loop v6 — DATA back-fill (DRY RUN). MySQL 8 / EspoCRM `renewal` table.
-- Safe to run repeatedly: wraps all DML in BEGIN ... ROLLBACK so nothing persists.
--
-- !!! DEPLOY SEQUENCE (read before running) !!!
-- MySQL DDL (ALTER/CREATE/DROP) is NOT transactional — it CANNOT be rolled back.
-- So this dry-run is DML-ONLY and ASSUMES Phase 1 already ran:
--   Phase 1 (add-only): apply the v6 entityDefs with new fields ADDED but old fields
--     NOT yet removed, then `Rebuild` in EspoCRM. This creates the new columns
--     (`pipeline_stage`, `disposition`, `ams_*`, `supabase_event_uuid`, the
--     `renewal_worksheet` table) WITHOUT dropping the legacy columns yet.
--   Phase 2 (this back-fill): run backfill_renewal_v6_commit.sql to copy
--     stage -> pipeline_stage, synthesize disposition, and seed RenewalWorksheet rows.
--   Phase 3 (drop-old): remove the legacy fields from entityDefs, Rebuild — EspoCRM
--     drops `stage`, `lost_reason`, `expected_commission`, `last_contact_*`, the
--     four checkboxes (data already migrated in Phase 2).
-- This dry-run previews Phase 2 DML only. Run it AFTER Phase 1 (new columns exist).
-- =====================================================================================

START TRANSACTION;

-- Guard: fail fast if Phase 1 hasn't added the new columns yet.
-- (information_schema check — aborts the dry run with a clear error.)
SET @has_ps := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'renewal'
                  AND COLUMN_NAME = 'pipeline_stage');
SET @has_ws := (SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'renewal_worksheet');
IF @has_ps = 0 OR @has_ws = 0 THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Phase 1 not run: pipeline_stage column and/or renewal_worksheet table missing. Apply v6 entityDefs (add-only) + Rebuild first.';
END IF;

-- 1) pipeline_stage <- stage (terminal outcomes collapse to Closed)
UPDATE renewal
SET pipeline_stage = CASE stage
    WHEN 'Renewed - Won' THEN 'Closed'
    WHEN 'Lost' THEN 'Closed'
    ELSE stage
END
WHERE stage IS NOT NULL AND stage <> '' AND pipeline_stage IS NULL;

-- 2) disposition <- stage + lost_reason  (matches Hermes LEGACY_LOST_REASON_TO_DISPOSITION)
UPDATE renewal
SET disposition = CASE
    WHEN stage = 'Renewed - Won' THEN 'renewed'
    WHEN stage = 'Lost' AND lost_reason = 'Price'         THEN 'lost_price'
    WHEN stage = 'Lost' AND lost_reason = 'Coverage'      THEN 'lost_coverage'
    WHEN stage = 'Lost' AND lost_reason = 'Unresponsive'  THEN 'lost_no_response'
    WHEN stage = 'Lost' AND lost_reason = 'Moved carrier' THEN 'rewritten'
    WHEN stage = 'Lost'                                 THEN 'do_not_renew'  -- unmapped/Other
    ELSE ''
END
WHERE stage IN ('Renewed - Won','Lost') AND (disposition IS NULL OR disposition = '');

-- 3) Seed one RenewalWorksheet per Renewal (1:1), copying the four checkboxes.
--    lob_variant derived from line_of_business. Column names follow the
--    RenewalWorksheet entityDefs dbName (camelCase). VERIFY against the actual
--    renewal_worksheet columns after Phase 1 rebuild before the commit run.
INSERT INTO renewal_worksheet
    (id, name, lob_variant, state, completion_type, renewal_id, account_id,
     renewal_reviewed, account_confirmed, renewal_email_sent, ams_updated,
     notes, created_at, modified_at)
SELECT
    REPLACE(UUID(),'-',''), CONCAT(name, ' — Worksheet'),
    CASE line_of_business
        WHEN 'Personal Auto'      THEN 'personal_auto'
        WHEN 'Homeowners'         THEN 'homeowners'
        WHEN 'Commercial Auto'    THEN 'commercial_auto'
        WHEN 'General Liability'  THEN 'general_liability'
        WHEN 'Workers Comp'       THEN 'workers_comp'
        ELSE 'default'
    END,
    'not_started', '',
    id, account_id,
    renewal_reviewed, account_confirmed, renewal_email_sent, ams_updated,
    renewal_notes, NOW(), NOW()
FROM renewal
WHERE deleted = 0;

-- ===== PREVIEW (read-only) =====
SELECT 'pipeline_stage mapping' AS check_name;
SELECT stage, pipeline_stage, COUNT(*) n FROM renewal GROUP BY stage, pipeline_stage ORDER BY stage;
SELECT 'disposition mapping' AS check_name;
SELECT stage, lost_reason, disposition, COUNT(*) n FROM renewal GROUP BY stage, lost_reason, disposition ORDER BY stage;
SELECT 'worksheet seed count' AS check_name;
SELECT lob_variant, COUNT(*) n FROM renewal_worksheet GROUP BY lob_variant ORDER BY lob_variant;
SELECT 'checkbox carry-over sample' AS check_name;
SELECT r.id, r.renewal_reviewed, r.account_confirmed, r.renewal_email_sent, r.ams_updated,
       w.renewal_reviewed AS ws_reviewed, w.account_confirmed AS ws_confirmed
FROM renewal r JOIN renewal_worksheet w ON w.renewal_id = r.id
WHERE r.renewal_reviewed = 1 OR r.account_confirmed = 1 OR r.renewal_email_sent = 1 OR r.ams_updated = 1
LIMIT 20;

ROLLBACK;
-- Nothing persisted. Inspect the SELECT outputs above. When correct, run backfill_renewal_v6_commit.sql.
