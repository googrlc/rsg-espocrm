<?php

namespace Espo\Custom\Hooks\RenewalWorksheet;

use Espo\Core\Hook\Hook\AfterSave as AfterSaveHook;
use Espo\Custom\Classes\RenewalWorksheet\WorksheetWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * RenewalWorksheet AfterSave:
 *
 * Fires worksheet.completed webhook to Hermes when state transitions to "completed".
 * The webhook feeds the Supabase ledger only — no MCP action triggered here.
 */
class AfterSave implements AfterSaveHook
{
    public function __construct(
        private WorksheetWebhookDispatcher $dispatcher
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $state = (string) ($entity->get('state') ?? '');

        if ($state !== 'completed') {
            return;
        }

        $previousState = (string) ($entity->getFetched('state') ?? '');

        if ($previousState === 'completed') {
            return;
        }

        $this->dispatcher->dispatch($entity);
    }
}
