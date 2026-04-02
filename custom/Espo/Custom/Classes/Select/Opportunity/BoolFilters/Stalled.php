<?php
namespace Espo\Custom\Classes\Select\Opportunity\BoolFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Bool\Filter;

class Stalled implements Filter
{
    public function apply(SelectBuilder $queryBuilder, string $boolFilterName): void
    {
        $queryBuilder->where(["stage" => "Stalled"]);
    }
}
