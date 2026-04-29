<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Expired implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $now = date('Y-m-d');

        $queryBuilder->where([
            'OR' => [
                ['status' => 'Expired'],
                [
                    'expiration_date<' => $now,
                    'status!=' => 'Cancelled',
                ],
            ],
        ]);
    }
}
