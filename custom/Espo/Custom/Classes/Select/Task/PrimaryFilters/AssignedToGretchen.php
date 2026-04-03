<?php
namespace Espo\Custom\Classes\Select\Task\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\EntityManager;

class AssignedToGretchen implements Filter
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function apply(SelectBuilder $queryBuilder): void
    {
        // Find Gretchen's user by name
        $gretchen = $this->entityManager
            ->getRDBRepository('User')
            ->where(['userName' => 'gretchcoates'])
            ->findOne();

        if ($gretchen) {
            $queryBuilder->where([
                'assignedUserId' => $gretchen->getId(),
                'status!=' => ['Completed', 'Cancelled'],
            ]);
        } else {
            // Fallback: find by first name
            $gretchen = $this->entityManager
                ->getRDBRepository('User')
                ->where(['firstName' => 'Gretchen'])
                ->findOne();

            if ($gretchen) {
                $queryBuilder->where([
                    'assignedUserId' => $gretchen->getId(),
                    'status!=' => ['Completed', 'Cancelled'],
                ]);
            }
        }
    }
}
