<?php

namespace Espo\Custom\Hooks\Account;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Account\AccountPlaybookManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SyncCrossSellPlaybooks implements AfterSave
{
    public function __construct(
        private AccountPlaybookManager $accountPlaybookManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->accountPlaybookManager->syncForAccount($entity);
    }
}
