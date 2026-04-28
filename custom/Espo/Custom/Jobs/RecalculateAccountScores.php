<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Custom\Classes\Account\AccountHealthManager;
use Espo\ORM\EntityManager;

/**
 * Daily refresh of every account's health scoring (scoreTotal, scoreTier,
 * scoreBundleDepth, etc.) via AccountHealthManager. Hooks already keep the
 * score current on relevant saves; this job is the safety net for accounts
 * that did not see a write during the day.
 */
class RecalculateAccountScores implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private AccountHealthManager $accountHealthManager
    ) {}

    public function run(): void
    {
        $count = 0;

        $pdo = $this->entityManager->getPDO();
        $stmt = $pdo->query('SELECT id FROM account WHERE deleted = 0');
        if ($stmt === false) {
            return;
        }

        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $accountId) {
            $accountId = (string) $accountId;
            if ($accountId === '') {
                continue;
            }
            $this->accountHealthManager->refreshByAccountId($accountId);
            $count++;
        }

        $GLOBALS['log']->info('RecalculateAccountScores: refreshed ' . $count . ' accounts.');
    }
}
