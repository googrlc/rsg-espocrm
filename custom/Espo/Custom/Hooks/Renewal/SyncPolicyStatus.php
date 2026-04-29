<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Modules\RsgCore\Classes\Renewal\RenewalOrchestrator;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SyncPolicyStatus implements AfterSave
{
    public function __construct(
        private RenewalOrchestrator $renewalOrchestrator
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->renewalOrchestrator->syncPolicyFromRenewal($entity);
    }
}
