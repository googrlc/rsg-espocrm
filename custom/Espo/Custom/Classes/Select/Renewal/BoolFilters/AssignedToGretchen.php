<?php
namespace Espo\Custom\Classes\Select\Renewal\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\EntityManager;

class AssignedToGretchen implements Filter
{
    public function __construct(private EntityManager $entityManager) {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $gretchen = $this->entityManager
            ->getRDBRepository('User')
            ->where(['userName' => 'gretchcoates'])
            ->findOne();

        if (!$gretchen) {
            $gretchen = $this->entityManager
                ->getRDBRepository('User')
                ->where(['firstName' => 'Gretchen'])
                ->findOne();
        }

        if ($gretchen) {
            $orGroupBuilder->add(
                Comparison::equal(Expression::column('assignedUserId'), $gretchen->getId())
            );
        }
    }
}
