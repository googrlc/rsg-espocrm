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
        // v6: commission ledger triggers on a WIN disposition (renewed/rewritten)
        // instead of the legacy stage = 'Renewed - Won'.
        $disposition = (string) ($entity->get('disposition') ?? '');
        if (!in_array($disposition, ['renewed', 'rewritten'], true)) {
            return;
        }

        $this->commissionLedgerManager->upsertFromRenewal($entity);
    }
}
