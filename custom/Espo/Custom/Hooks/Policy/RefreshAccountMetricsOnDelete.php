<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Modules\RsgCore\Classes\Policy\PolicyAccountSync;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\RemoveOptions;

class RefreshAccountMetricsOnDelete implements AfterRemove
{
    public function __construct(
        private PolicyAccountSync $policyAccountSync
    ) {}

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $this->policyAccountSync->refreshAccountMetricsByPolicy($entity);
    }
}
