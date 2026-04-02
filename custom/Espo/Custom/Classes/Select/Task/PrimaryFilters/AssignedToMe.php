<?php
namespace Espo\Custom\Classes\Select\Task\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;
use Espo\Entities\User;

class AssignedToMe implements Filter
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'assignedUserId' => $this->user->getId(),
            'status!=' => ['Completed', 'Cancelled'],
        ]);
    }
}
