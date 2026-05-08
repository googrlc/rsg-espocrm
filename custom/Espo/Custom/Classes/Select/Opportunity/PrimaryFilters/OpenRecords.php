<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class OpenRecords implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'stage!=' => ['Closed Won', 'Closed Lost'],
        ]);
    }
}
