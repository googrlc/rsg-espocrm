<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Actual implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["status!=" => ["Converted", "Dead", "Recycled"]]);
    }
}
