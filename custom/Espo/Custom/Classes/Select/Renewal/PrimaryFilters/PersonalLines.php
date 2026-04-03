<?php
namespace Espo\Custom\Classes\Select\Renewal\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class PersonalLines implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'lineOfBusiness' => [
                'Personal Auto',
                'Homeowners',
                'Renters',
                'Condo',
                'Dwelling Fire',
                'Motorcycle',
                'Boat',
                'RV',
            ],
        ]);
    }
}
