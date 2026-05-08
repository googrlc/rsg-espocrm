<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use DateTimeImmutable;
use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class FutureXDate implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        $queryBuilder->where([
            'xDate>' => $today,
            'status!=' => ['Disqualified', 'Converted', 'Converted to Opportunity'],
        ]);
    }
}
