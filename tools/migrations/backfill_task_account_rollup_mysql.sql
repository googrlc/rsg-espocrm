-- Backfill Task account rollup columns before deploying account-only task views.
-- Idempotent: safe to run more than once.

START TRANSACTION;

UPDATE task
SET account_id = linked_account_id
WHERE (account_id IS NULL OR account_id = '')
  AND linked_account_id IS NOT NULL
  AND linked_account_id <> '';

UPDATE task
SET linked_account_id = account_id
WHERE (linked_account_id IS NULL OR linked_account_id = '')
  AND account_id IS NOT NULL
  AND account_id <> '';

COMMIT;

SELECT
  COUNT(*) AS task_count,
  SUM(CASE WHEN linked_account_id IS NOT NULL AND linked_account_id <> '' THEN 1 ELSE 0 END) AS with_linked_account_id,
  SUM(CASE WHEN account_id IS NOT NULL AND account_id <> '' THEN 1 ELSE 0 END) AS with_account_id,
  SUM(CASE WHEN (account_id IS NULL OR account_id = '') AND linked_account_id IS NOT NULL AND linked_account_id <> '' THEN 1 ELSE 0 END) AS linked_only_remaining,
  SUM(CASE WHEN (linked_account_id IS NULL OR linked_account_id = '') AND account_id IS NOT NULL AND account_id <> '' THEN 1 ELSE 0 END) AS account_only_remaining
FROM task;
