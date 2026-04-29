<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class CommercialOnly implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'OR' => [
                ['line_of_business*' => '%auto%'],
                ['line_of_business*' => '%liability%'],
                ['line_of_business*' => '%workers%'],
                ['line_of_business*' => '%comp%'],
                ['line_of_business*' => '%commercial%'],
                ['line_of_business*' => '%property%'],
                ['line_of_business*' => '%BOP%'],
                ['line_of_business*' => '%umbrella%'],
                ['line_of_business*' => '%professional%'],
                ['line_of_business*' => '%transport%'],
            ],
        ]);
    }
}
