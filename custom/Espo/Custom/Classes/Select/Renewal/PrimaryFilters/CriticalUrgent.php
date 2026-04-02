<?php
namespace Espo\Custom\Classes\Select\Renewal\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class CriticalUrgent implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'outcome' => 'Pending',
            'urgency' => ['Critical (0-7 days)', 'Urgent (8-14 days)'],
        ]);
    }
}
