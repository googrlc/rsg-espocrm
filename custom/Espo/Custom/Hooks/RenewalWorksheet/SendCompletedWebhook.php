<?php

namespace Espo\Custom\Hooks\RenewalWorksheet;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\RenewalWorksheet\WorksheetCompletedWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Renewal Loop v6 — emit worksheet.completed to Hermes only when state
 * transitions to "completed" (SubmitWorksheet is the path that does this).
 */
class SendCompletedWebhook implements AfterSave
{
    public function __construct(
        private WorksheetCompletedWebhookDispatcher $dispatcher
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $state = (string) ($entity->get('state') ?? '');
        $fetchedState = (string) ($entity->getFetched('state') ?? '');

        if ($state === 'completed' && $fetchedState !== 'completed') {
            $this->dispatcher->dispatch($entity);
        }
    }
}
