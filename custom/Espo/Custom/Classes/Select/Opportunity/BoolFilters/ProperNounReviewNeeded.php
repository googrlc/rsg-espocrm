<?php
namespace Espo\Custom\Classes\Select\Opportunity\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\SelectBuilder;

class ProperNounReviewNeeded implements Filter
{
    public function apply(SelectBuilder $queryBuilder, string $boolFilterName): void
    {
        $queryBuilder->where(['properNounReviewNeeded' => true]);
    }
}
