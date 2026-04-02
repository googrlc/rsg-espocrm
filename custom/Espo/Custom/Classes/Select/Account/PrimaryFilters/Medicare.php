<?php
namespace Espo\Custom\Classes\Select\Account\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class Medicare implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["accountType" => "Medicare"]);
    }
}
