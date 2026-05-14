<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\ORM\EntityManager;

class MigrateTaskAccountRollup implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function run(): void
    {
        $pdo = $this->entityManager->getPDO();

        $updated = $pdo->exec(
            "UPDATE task t
             LEFT JOIN account a ON a.id = t.linked_account_id AND a.deleted = 0
             SET
                t.account_id = t.linked_account_id,
                t.account_name = COALESCE(NULLIF(t.linked_account_name, ''), a.name, t.account_name)
             WHERE t.deleted = 0
                AND (t.account_id IS NULL OR t.account_id = '')
                AND t.linked_account_id IS NOT NULL
                AND t.linked_account_id <> ''"
        );

        $mirrored = $pdo->exec(
            "UPDATE task t
             LEFT JOIN account a ON a.id = t.account_id AND a.deleted = 0
             SET
                t.linked_account_id = t.account_id,
                t.linked_account_name = COALESCE(NULLIF(t.account_name, ''), a.name, t.linked_account_name)
             WHERE t.deleted = 0
                AND t.account_id IS NOT NULL
                AND t.account_id <> ''
                AND (t.linked_account_id IS NULL OR t.linked_account_id = '')"
        );

        $GLOBALS['log']->info(sprintf(
            'MigrateTaskAccountRollup: backfilled %d account links and mirrored %d legacy links.',
            (int) $updated,
            (int) $mirrored
        ));
    }
}
