<?php
namespace Espo\Custom\Classes\Select\Opportunity\BoolFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Condition;
use Espo\Core\Select\Bool\Filter;

class Stalled implements Filter
{
    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $cutoff = (new \DateTime('-30 days'))->format('Y-m-d H:i:s');

        $orGroupBuilder->add(
            Condition::and(
                Condition::notIn(
                    Condition::column('stage'),
                    ['Closed Won', 'Closed Lost']
                ),
                Condition::less(
                    Condition::column('modifiedAt'),
                    $cutoff
                )
            )
        );
    }
}
