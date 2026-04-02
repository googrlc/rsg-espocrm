<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Lost implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["stage" => "Lost"]);
    }
}
