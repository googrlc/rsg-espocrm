<?php
namespace Espo\Custom\Classes\Select\Meeting\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Planned implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["status" => "Planned"]);
    }
}
