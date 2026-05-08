<?php
namespace Espo\Custom\Classes\Select\ActivityLog\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class BidirectionalSync implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'syncDirection' => [
                'Outbound CRM -> AMS',
                'Inbound AMS -> CRM',
            ],
        ]);
    }
}
