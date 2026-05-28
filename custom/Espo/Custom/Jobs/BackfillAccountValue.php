<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Custom\Classes\Account\AccountValueManager;
use Espo\ORM\EntityManager;

/**
 * One-off backfill: populates all CLV fields on every account via
 * AccountValueManager::refreshByAccountId. Uses the existing SILENT +
 * SKIP_VALUE_SNAPSHOT_OPTION save pattern so no cascading hooks fire.
 */
class BackfillAccountValue implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private AccountValueManager $accountValueManager
    ) {}

    public function run(): void
    {
        $pdo = $this->entityManager->getPDO();
        $stmt = $pdo->query('SELECT id FROM account WHERE deleted = 0');
        if ($stmt === false) {
            $GLOBALS['log']->warning('BackfillAccountValue: SELECT failed.');
            return;
        }

        $count = 0;
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $accountId) {
            $accountId = (string) $accountId;
            if ($accountId === '') {
                continue;
            }
            $this->accountValueManager->refreshByAccountId($accountId);
            $count++;
        }

        $GLOBALS['log']->info('BackfillAccountValue: refreshed ' . $count . ' accounts.');
    }
}
