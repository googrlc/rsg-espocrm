<?php
namespace Espo\Custom\Classes\Select\Case_\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Closed implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["status" => "Closed"]);
    }
}
