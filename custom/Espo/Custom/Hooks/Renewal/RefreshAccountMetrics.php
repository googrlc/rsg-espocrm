<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Modules\RsgCore\Classes\Policy\PolicyAccountSync;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class RefreshAccountMetrics implements AfterSave
{
    public function __construct(
        private PolicyAccountSync $policyAccountSync
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $accountIds = array_filter(array_unique([
            $entity->get('accountId'),
            $entity->getFetched('accountId'),
        ]));

        foreach ($accountIds as $accountId) {
            $this->policyAccountSync->refreshAccountMetricsById((string) $accountId);
        }
    }
}
