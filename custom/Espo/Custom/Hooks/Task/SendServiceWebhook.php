<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Task\ServiceActivityLogger;
use Espo\Custom\Classes\Task\ServiceWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SendServiceWebhook implements AfterSave
{
    public function __construct(
        private ServiceWebhookDispatcher $serviceWebhookDispatcher,
        private ServiceActivityLogger $serviceActivityLogger
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->serviceActivityLogger->log($entity);
        $this->serviceWebhookDispatcher->dispatch($entity);
    }
}
