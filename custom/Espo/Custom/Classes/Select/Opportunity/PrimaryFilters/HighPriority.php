<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class HighPriority implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'priority' => ['Hot', 'Warm'],
            'stage!=' => ['Closed Won', 'Closed Lost'],
        ]);
    }
}
