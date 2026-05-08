<?php
namespace Espo\Custom\Classes\Select\Opportunity\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class MissingData implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'or' => [
                ['accountId' => null],
                ['lineOfBusiness' => null],
                ['lineOfBusiness' => ''],
                ['estimatedPremium' => null],
                ['nextFollowUpDate' => null],
            ],
            'stage!=' => ['Closed Won', 'Closed Lost'],
        ]);
    }
}
