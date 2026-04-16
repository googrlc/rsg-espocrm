<?php
namespace Espo\Custom\Classes\Select\Task\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class WaitingOnCarrier implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'status' => 'Waiting on Carrier',
        ]);
    }
}
