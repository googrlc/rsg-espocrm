<?php
namespace Espo\Custom\Classes\Select\Account\PrimaryFilters;

use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Select\Primary\Filter;

class RecentlyCreated implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            "createdAt>=" => date("Y-m-d H:i:s", strtotime("-30 days"))
        ]);
    }
}
