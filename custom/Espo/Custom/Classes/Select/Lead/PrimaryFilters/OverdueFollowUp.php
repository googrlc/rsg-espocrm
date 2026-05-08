<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use DateTimeImmutable;
use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class OverdueFollowUp implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        $queryBuilder->where([
            'nextFollowUpDate<' => $today,
            'status!=' => ['Disqualified', 'Converted', 'Converted to Opportunity'],
        ]);
    }
}
