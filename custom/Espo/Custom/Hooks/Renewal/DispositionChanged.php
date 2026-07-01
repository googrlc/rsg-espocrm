<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Renewal\DispositionWebhookDispatcher;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DispositionChanged implements AfterSave
{
    public function __construct(
        private DispositionWebhookDispatcher $dispatcher
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->dispatcher->dispatch($entity);
    }
}
