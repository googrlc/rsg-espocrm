<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Custom\Classes\Renewal\RenewalOrchestrator;
use Espo\ORM\EntityManager;

/**
 * Creates/updates Renewal records from policies based on expiration + LOB windows.
 * Policies that are not saved often still enter the pipeline via this job.
 */
class SyncRenewalsFromPolicies implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager,
        private RenewalOrchestrator $renewalOrchestrator
    ) {}

    public function run(): void
    {
        $policies = $this->entityManager
            ->getRDBRepository('Policy')
            ->where([
                'deleted' => false,
                'OR' => [
                    ['status' => 'Active'],
                    ['status' => 'Up for Renewal'],
                    ['status' => 'Renewing'],
                ],
            ])
            ->find();

        $count = 0;
        foreach ($policies as $policy) {
            if (!$policy->get('accountId') || !$policy->get('expirationDate')) {
                continue;
            }
            $this->renewalOrchestrator->syncFromPolicy($policy);
            $count++;
        }

        $GLOBALS['log']->info('SyncRenewalsFromPolicies: processed ' . $count . ' policies.');
    }
}
