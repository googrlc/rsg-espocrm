<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Modules\RsgCore\Classes\Renewal\RenewalOrchestrator;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveFields implements BeforeSave
{
    public function __construct(
        private RenewalOrchestrator $renewalOrchestrator
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $this->renewalOrchestrator->applyDerivedFields($entity);
    }
}
