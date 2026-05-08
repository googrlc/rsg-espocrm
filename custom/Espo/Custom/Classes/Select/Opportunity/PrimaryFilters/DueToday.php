<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use DateTimeImmutable;
use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class DueToday implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');

        $queryBuilder->where([
            'stage!=' => ['Closed Won', 'Closed Lost'],
            'closeDate' => $today,
        ]);
    }
}
