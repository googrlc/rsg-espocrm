<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Commission\CommissionLedgerManager;
use Espo\Custom\Classes\Renewal\RenewalOrchestrator;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class ActivateAutomation implements AfterSave
{
    private const LIVE_POLICY_STATUSES = [
        'Active',
        'Up for Renewal',
        'Renewing',
        'Renewed',
    ];

    public function __construct(
        private CommissionLedgerManager $commissionLedgerManager,
        private RenewalOrchestrator $renewalOrchestrator
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $status = (string) ($entity->get('status') ?? '');
        $fetchedStatus = (string) ($entity->getFetched('status') ?? '');

        if (
            in_array($status, self::LIVE_POLICY_STATUSES, true) &&
            !in_array($fetchedStatus, self::LIVE_POLICY_STATUSES, true)
        ) {
            $this->commissionLedgerManager->upsertFromPolicy($entity);
        }

        $this->renewalOrchestrator->syncFromPolicy($entity);
    }
}
