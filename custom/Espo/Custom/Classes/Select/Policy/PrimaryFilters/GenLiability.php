<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class GenLiability implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'OR' => [
                ['line_of_business*' => '%general liability%'],
                ['line_of_business*' => '%GL%'],
            ],
        ]);
    }
}
