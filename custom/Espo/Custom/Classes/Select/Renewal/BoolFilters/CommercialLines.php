<?php
namespace Espo\Custom\Classes\Select\Renewal\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;

class CommercialLines implements Filter
{
    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $orGroupBuilder->add(
            Comparison::in(
                Expression::column('lineOfBusiness'),
                [
                    'Commercial Auto',
                    'General Liability',
                    'Workers Comp',
                    'Commercial Property',
                    'BOP',
                    'Professional Liability',
                    'Umbrella',
                    'Builders Risk',
                    'Inland Marine',
                    'Garagekeepers',
                    'Commercial Package',
                ]
            )
        );
    }
}
