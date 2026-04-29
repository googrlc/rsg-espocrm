<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class PersonalLines implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'OR' => [
                ['line_of_business*' => '%personal%'],
                ['line_of_business*' => '%homeowner%'],
                ['line_of_business*' => '%home%'],
            ],
        ]);
    }
}
