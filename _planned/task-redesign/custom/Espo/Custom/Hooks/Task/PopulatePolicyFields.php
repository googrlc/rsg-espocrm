<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Auto-populates the Task's policy reference fields (policyType, policyNumber,
 * carrier, policyEffectiveDate, policyExpirationDate) from the linked Policy.
 *
 * Server-side safety net for all creation paths (UI, API, email). Only fills
 * fields that are currently empty, so manual entries / UI client-side fills are
 * never clobbered.
 */
class PopulatePolicyFields implements BeforeSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $policyId = trim((string) ($entity->get('policyId') ?? ''));

        if ($policyId === '') {
            return;
        }

        $policy = $this->entityManager->getEntityById('Policy', $policyId);

        if (!$policy) {
            return;
        }

        $map = [
            'policyType' => trim((string) (
                $policy->get('line_of_business')
                ?: $policy->get('line_of_business_raw')
                ?: $policy->get('business_type')
                ?: ''
            )),
            'policyNumber' => trim((string) ($policy->get('policy_number') ?? '')),
            'carrier' => trim((string) ($policy->get('carrier') ?? '')),
            'policyEffectiveDate' => $policy->get('effective_date'),
            'policyExpirationDate' => $policy->get('expiration_date'),
        ];

        foreach ($map as $field => $value) {
            $current = $entity->get($field);

            if (($current === null || $current === '') && $value !== null && $value !== '') {
                $entity->set($field, $value);
            }
        }
    }
}
