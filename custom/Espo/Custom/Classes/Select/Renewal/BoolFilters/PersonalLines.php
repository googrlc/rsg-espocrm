<?php
namespace Espo\Custom\Classes\Select\Renewal\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;

class PersonalLines implements Filter
{
    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $orGroupBuilder->add(
            Comparison::in(
                Expression::column('lineOfBusiness'),
                [
                    'Personal Auto',
                    'Homeowners',
                    'Renters',
                    'Condo',
                    'Dwelling Fire',
                    'Motorcycle',
                    'Boat',
                    'RV',
                ]
            )
        );
    }
}
