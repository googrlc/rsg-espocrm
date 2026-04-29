<?php
namespace Espo\Custom\Classes\Select\Account\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Prospects implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            "account_type" => "Prospect"
        ]);
    }
}
