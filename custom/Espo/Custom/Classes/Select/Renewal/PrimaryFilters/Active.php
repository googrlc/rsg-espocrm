<?php
namespace Espo\Custom\Classes\Select\Renewal\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Active implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'stage' => [
                'Identified',
                'Outreach Sent',
                'Quote Requested',
                'Proposal Sent',
                'Negotiating',
            ],
        ]);
    }
}
