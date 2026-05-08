<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use DateTimeImmutable;
use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class DueThisWeek implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $start = new DateTimeImmutable('today');
        $end = $start->modify('+6 days');

        $queryBuilder->where([
            'stage!=' => ['Closed Won', 'Closed Lost'],
            'closeDate>=' => $start->format('Y-m-d'),
            'closeDate<=' => $end->format('Y-m-d'),
        ]);
    }
}
