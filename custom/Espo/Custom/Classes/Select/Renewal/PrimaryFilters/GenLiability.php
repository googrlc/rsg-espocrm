<?php
namespace Espo\Custom\Classes\Select\Renewal\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class GenLiability implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(["line_of_business" => "General Liability"]);
    }
}
