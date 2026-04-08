<?php
namespace Espo\Custom\Classes\Select\ActivityLog\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class PolicyChanges implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            "activityType" => [
                "Endorsement",
                "Premium Change",
                "Coverage Add",
                "Coverage Remove",
                "Cancellation",
            ],
        ]);
    }
}
