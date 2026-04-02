<?php
namespace Espo\Custom\Classes\Select\Commission\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Reconciled implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where(['reconciliationStatus' => 'Reconciled']);
    }
}
