<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Account\AccountHealthManager;
use Espo\Custom\Classes\Task\ServiceActivityLogger;
use Espo\Custom\Classes\Task\ServiceWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SendServiceWebhook implements AfterSave
{
    public function __construct(
        private ServiceWebhookDispatcher $serviceWebhookDispatcher,
        private ServiceActivityLogger $serviceActivityLogger,
        private AccountHealthManager $accountHealthManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->serviceActivityLogger->log($entity);
        $this->serviceWebhookDispatcher->dispatch($entity);

        $automationKey = trim((string) ($entity->get('automationKey') ?? ''));
        if (str_starts_with($automationKey, 'account-playbook:')) {
            return;
        }

        $accountIds = array_filter(array_unique([
            $entity->get('linkedAccountId'),
            $entity->getFetched('linkedAccountId'),
        ]));

        foreach ($accountIds as $accountId) {
            $this->accountHealthManager->refreshByAccountId((string) $accountId);
        }
    }
}
