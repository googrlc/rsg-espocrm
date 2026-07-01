<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Renewal\RenewalDispositionWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Renewal Loop v6 — emit renewal.disposition_changed to Hermes when the
 * disposition or pipeline_stage changes on save.
 */
class DispositionChanged implements AfterSave
{
    public function __construct(
        private RenewalDispositionWebhookDispatcher $dispatcher
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->dispatcher->dispatch($entity);
    }
}
