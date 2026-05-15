<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class CallbackDue implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $today = date('Y-m-d');

        $queryBuilder->where([
            'status' => 'Nurture',
            'callbackDate<=' => $today,
        ]);
    }
}
