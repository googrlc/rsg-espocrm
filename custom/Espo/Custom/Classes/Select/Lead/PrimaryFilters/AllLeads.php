<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class AllLeads implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
    }
}
