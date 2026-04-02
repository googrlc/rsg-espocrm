<?php
namespace Espo\Custom\Classes\Select\Commission\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class All implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        // no filter - show all
    }
}
