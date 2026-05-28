<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Custom\Classes\Policy\PolicyHealthManager;
use Espo\ORM\EntityManager;

/**
 * One-off backfill: populates Policy.policy_health for every existing policy
 * without firing BeforeSave/AfterSave hooks (skips PolicyAccountSync's auto
 * status-upgrade and SendPolicyCorrectionWebhook). Computes via the same
 * logic as PolicyHealthManager, then writes via a direct UPDATE.
 *
 * Pair with RecalculateAccountScores afterward to refresh Account.account_status.
 */
class BackfillPolicyHealth implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private PolicyHealthManager $policyHealthManager
    ) {}

    public function run(): void
    {
        $pdo = $this->entityManager->getPDO();
        $stmt = $pdo->query('SELECT id FROM policy WHERE deleted = 0');
        if ($stmt === false) {
            $GLOBALS['log']->warning('BackfillPolicyHealth: SELECT failed.');
            return;
        }

        $update = $pdo->prepare('UPDATE policy SET policy_health = :health WHERE id = :id');

        $count = 0;
        $skipped = 0;
        $byHealth = [];

        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $policyId) {
            $policyId = (string) $policyId;
            if ($policyId === '') {
                continue;
            }

            $policy = $this->entityManager->getEntityById('Policy', $policyId);
            if (!$policy) {
                $skipped++;
                continue;
            }

            $this->policyHealthManager->applyToPolicy($policy);
            $health = (string) ($policy->get('policy_health') ?? '');

            if ($health === '') {
                $skipped++;
                continue;
            }

            $update->execute([':health' => $health, ':id' => $policyId]);
            $count++;
            $byHealth[$health] = ($byHealth[$health] ?? 0) + 1;
        }

        $summary = json_encode($byHealth);
        $GLOBALS['log']->info(
            "BackfillPolicyHealth: updated {$count} policies, skipped {$skipped}. Breakdown: {$summary}"
        );
    }
}
