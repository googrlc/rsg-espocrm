<?php
namespace Espo\Custom\Classes\Select\Lead\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder;

class MyLeads implements Filter
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
        ]);
    }
}
