<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Commission\CommissionLedgerManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class CreateCommissionLedger implements AfterSave
{
    private const COMMISSION_DISPOSITIONS = ['renewed', 'rewritten'];

    public function __construct(
        private CommissionLedgerManager $commissionLedgerManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $disposition = (string) ($entity->get('disposition') ?? '');
        if (!in_array($disposition, self::COMMISSION_DISPOSITIONS, true)) {
            return;
        }

        $previousDisposition = (string) ($entity->getFetched('disposition') ?? '');
        if ($previousDisposition === $disposition) {
            return;
        }

        $this->commissionLedgerManager->upsertFromRenewal($entity);
    }
}
