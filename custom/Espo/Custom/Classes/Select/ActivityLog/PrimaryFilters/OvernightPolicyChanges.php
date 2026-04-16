<?php
namespace Espo\Custom\Classes\Select\ActivityLog\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class OvernightPolicyChanges implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $since = date('Y-m-d H:i:s', strtotime('-1 day'));

        $queryBuilder->where([
            'activityType' => [
                'Endorsement',
                'Premium Change',
                'Coverage Add',
                'Coverage Remove',
                'Cancellation',
            ],
            'dateTime>=' => $since,
        ]);
    }
}
