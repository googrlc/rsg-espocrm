<?php

namespace Espo\Custom\Hooks\ActivityLog;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Account\AccountHealthManager;
use Espo\Custom\Classes\ActivityLog\AccountRescueManager;
use Espo\Custom\Classes\ActivityLog\ServiceTriageManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class CreateServiceTask implements AfterSave
{
    public function __construct(
        private ServiceTriageManager $serviceTriageManager,
        private AccountRescueManager $accountRescueManager,
        private AccountHealthManager $accountHealthManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        // Rescue runs first so triage can skip when both apply (single open task per ActivityLog)
        $this->accountRescueManager->createTaskFromActivity($entity);
        $this->serviceTriageManager->createTaskFromActivity($entity);

        $accountId = trim((string) ($entity->get('accountId') ?? ''));
        if ($accountId !== '') {
            $this->accountHealthManager->refreshByAccountId($accountId);
        }
    }
}
