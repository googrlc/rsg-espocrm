<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class HotLeads implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'or' => [
                ['priority' => 'Hot'],
                ['leadScore>=' => 80],
            ],
            'status!=' => ['Disqualified', 'Converted', 'Converted to Opportunity'],
        ]);
    }
}
