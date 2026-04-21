<?php
namespace Espo\Custom\Classes\Select\Commission\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class HighVariance implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'status' => 'Posted',
            'OR' => [
                ['variancePercent>' => 15],
                ['variancePercent<' => -15],
            ],
        ]);
    }
}
