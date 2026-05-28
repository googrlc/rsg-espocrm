<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Account\AccountValueManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class RefreshAccountValue implements AfterSave
{
    private const WATCHED_FIELDS = [
        'commissionAmount',
        'status',
        'effective_date',
    ];

    public function __construct(
        private AccountValueManager $accountValueManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew() && !$this->hasRelevantChange($entity)) {
            return;
        }

        $accountIds = array_unique(array_filter([
            (string) ($entity->get('accountId') ?? ''),
            (string) ($entity->getFetched('accountId') ?? ''),
        ]));

        foreach ($accountIds as $accountId) {
            $this->accountValueManager->refreshByAccountId($accountId);
        }
    }

    private function hasRelevantChange(Entity $entity): bool
    {
        foreach (self::WATCHED_FIELDS as $field) {
            if ($entity->isAttributeChanged($field)) {
                return true;
            }
        }

        return false;
    }
}
