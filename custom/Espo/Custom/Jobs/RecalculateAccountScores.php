<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Custom\Classes\Account\AccountHealthManager;
use Espo\Custom\Classes\Account\AccountValueManager;
use Espo\Modules\RsgCore\Classes\Policy\PolicyAccountSync;
use Espo\ORM\EntityManager;

/**
 * Daily refresh of every account's health scoring (scoreTotal, scoreTier,
 * scoreBundleDepth, etc.) via AccountHealthManager, then CLV via
 * AccountValueManager. Also refreshes total_active_premium and related
 * policy-derived metrics via PolicyAccountSync so that hard-deleted policies
 * don't leave stale values. Hooks already keep these current on relevant
 * saves; this job is the safety net for accounts that did not see a write
 * during the day. CLV runs after health so it can read the freshly-computed
 * score_tier for its retention lookup.
 */
class RecalculateAccountScores implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private AccountHealthManager $accountHealthManager,
        private AccountValueManager $accountValueManager,
        private PolicyAccountSync $policyAccountSync
    ) {}

    public function run(): void
    {
        $count = 0;

        $pdo = $this->entityManager->getPDO();
        $stmt = $pdo->query("SELECT id FROM account WHERE deleted = 0 AND (account_type IS NULL OR account_type NOT IN ('Carrier','MGA','Vendor/Partner'))");
        if ($stmt === false) {
            return;
        }

        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $accountId) {
            $accountId = (string) $accountId;
            if ($accountId === '') {
                continue;
            }
            $this->policyAccountSync->refreshAccountMetricsById($accountId);
            $this->accountHealthManager->refreshByAccountId($accountId);
            $this->accountValueManager->refreshByAccountId($accountId);
            $count++;
        }

        $GLOBALS['log']->info('RecalculateAccountScores: refreshed ' . $count . ' accounts.');
    }
}
