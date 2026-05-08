<?php
namespace Espo\Custom\Classes\Select\ActivityLog\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class SyncIncomplete implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'syncCompletionStatus' => [
                'Pending',
                'Failed',
            ],
        ]);
    }
}
