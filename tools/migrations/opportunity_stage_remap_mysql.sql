-- Opportunity stage remap: legacy server enum -> clean repo enum.
--
-- WHY: the live server's `opportunity.stage` enum had drifted to a messy legacy
-- set (Prospect, Qualify, Quote, Quoted, Proposal, Presented to Client,
-- Negotiate, Won - Bound, Bound / Renewed, Lost, Non-Renewal / Lost,
-- Renewal Notice Sent). The repo's canonical enum is:
--   Discovery, Quoting, Markets Out / Shopping, Quotes Complete,
--   Proposal Presented, Negotiation, Closed Won, Closed Lost
-- After deploying the new entityDefs/Opportunity.json, existing records still
-- hold the OLD values and would fall outside every kanban column until remapped.
--
-- PREREQUISITES: run AFTER deploying metadata + `rebuild`. Snapshot first.
--   docker exec app-mysql-1 mysqldump -u root -p espocrm opportunity > opp_backup.sql
--
-- USAGE (inside the Elestio box):
--   docker exec -i app-mysql-1 mysql -u root -p espocrm < opportunity_stage_remap_mysql.sql
--
-- IDEMPOTENT: each UPDATE only touches rows still holding a legacy value, so it
-- is safe to re-run. Review the mapping below before running — the renewal-era
-- stages (Renewal Notice Sent, Bound / Renewed) are judgement calls.

-- Early sales pipeline
UPDATE opportunity SET stage = 'Discovery'           WHERE stage IN ('Prospect', 'Qualify', 'Renewal Notice Sent');
UPDATE opportunity SET stage = 'Quoting'             WHERE stage IN ('Quote');
UPDATE opportunity SET stage = 'Quotes Complete'     WHERE stage IN ('Quoted');

-- Mid pipeline (Markets Out / Shopping already matches the new enum — no-op)
UPDATE opportunity SET stage = 'Proposal Presented'  WHERE stage IN ('Proposal', 'Presented to Client');
UPDATE opportunity SET stage = 'Negotiation'         WHERE stage IN ('Negotiate');

-- Closed
UPDATE opportunity SET stage = 'Closed Won'          WHERE stage IN ('Won - Bound', 'Bound / Renewed');
UPDATE opportunity SET stage = 'Closed Lost'         WHERE stage IN ('Lost', 'Non-Renewal / Lost');

-- Verification: anything left outside the canonical set is reported below.
SELECT stage AS unmapped_stage, COUNT(*) AS n
FROM opportunity
WHERE stage IS NOT NULL
  AND stage <> ''
  AND stage NOT IN (
      'Discovery', 'Quoting', 'Markets Out / Shopping', 'Quotes Complete',
      'Proposal Presented', 'Negotiation', 'Closed Won', 'Closed Lost'
  )
GROUP BY stage;
