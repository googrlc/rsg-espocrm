<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class CallbackNext14 implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $today = date('Y-m-d');
        $in14Days = date('Y-m-d', strtotime('+14 days'));

        $queryBuilder->where([
            'status' => 'Nurture',
            'callbackDate>=' => $today,
            'callbackDate<=' => $in14Days,
        ]);
    }
}
