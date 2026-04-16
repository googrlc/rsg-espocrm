<?php
namespace Espo\Custom\Classes\Select\Task\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class ServiceQueue implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'status!=' => ['Completed', 'Cancelled'],
            'taskType' => [
                'Client Service',
                'Policy Change',
                'Claims',
                'Follow Up',
                'Onboarding',
                'Admin',
            ],
        ]);
    }
}
