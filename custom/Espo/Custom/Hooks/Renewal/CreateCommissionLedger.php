<?php

namespace Espo\Custom\Hooks\Renewal;

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
        if ((string) ($entity->get('stage') ?? '') !== 'Renewed - Won') {
            return;
        }

        $this->commissionLedgerManager->upsertFromRenewal($entity);
    }
}
