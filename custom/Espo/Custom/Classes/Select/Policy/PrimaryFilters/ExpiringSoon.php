<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class ExpiringSoon implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $now = date('Y-m-d');
        $sixtyDays = date('Y-m-d', strtotime('+60 days'));

        $queryBuilder->where([
            'expiration_date>=' => $now,
            'expiration_date<=' => $sixtyDays,
        ]);
    }
}
