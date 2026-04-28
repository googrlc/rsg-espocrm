<?php

namespace Espo\Custom\Hooks\Account;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Account\AccountHealthManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveHealthSnapshot implements BeforeSave
{
    public function __construct(
        private AccountHealthManager $accountHealthManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($options->get(AccountHealthManager::SKIP_HEALTH_SNAPSHOT_OPTION) === true) {
            return;
        }

        $this->accountHealthManager->applyToAccount($entity);
    }
}
