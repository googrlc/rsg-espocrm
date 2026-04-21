<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Commission\CommissionLedgerManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class CreateCommissionLedger implements AfterSave
{
    public function __construct(
        private CommissionLedgerManager $commissionLedgerManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ((string) ($entity->get('stage') ?? '') !== 'Closed Won') {
            return;
        }

        $this->commissionLedgerManager->upsertFromOpportunity($entity);
    }
}
