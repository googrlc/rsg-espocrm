<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class LifeHealthLeads implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'or' => [
                ['leadType' => 'Life / Health'],
                ['insuranceInterest' => ['Life', 'Health', 'Medicare', 'Group Benefits']],
            ],
        ]);
    }
}
