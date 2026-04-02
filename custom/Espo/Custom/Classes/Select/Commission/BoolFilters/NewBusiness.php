<?php
namespace Espo\Custom\Classes\Select\Commission\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;

class NewBusiness implements Filter
{
    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $orGroupBuilder->add(
            Comparison::equal(Expression::column('sourceType'), 'New Business')
        );
    }
}
