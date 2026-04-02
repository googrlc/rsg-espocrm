<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Nurture implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["status" => "Nurture"]);
    }
}
