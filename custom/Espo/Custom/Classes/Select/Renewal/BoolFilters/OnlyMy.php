<?php
namespace Espo\Custom\Classes\Select\Renewal\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;
use Espo\Entities\User;

class OnlyMy implements Filter
{
    public function __construct(private User $user) {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $orGroupBuilder->add(
            Comparison::equal(Expression::column('assignedUserId'), $this->user->getId())
        );
    }
}
