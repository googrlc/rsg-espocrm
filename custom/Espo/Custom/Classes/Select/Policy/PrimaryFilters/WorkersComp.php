<?php
namespace Espo\Custom\Classes\Select\Policy\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class WorkersComp implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'OR' => [
                ['line_of_business*' => '%workers%'],
                ['line_of_business*' => '%comp%'],
                ['line_of_business*' => '%WC%'],
            ],
        ]);
    }
}
