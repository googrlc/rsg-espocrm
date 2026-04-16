<?php

namespace Espo\Custom\Hooks\ActivityLog;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\ActivityLog\ServiceTriageManager;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class CreateServiceTask implements AfterSave
{
    public function __construct(
        private ServiceTriageManager $serviceTriageManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->serviceTriageManager->createTaskFromActivity($entity);
    }
}
