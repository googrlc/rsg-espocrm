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
                ['lineOfBusiness*' => '%workers%'],
                ['lineOfBusiness*' => '%comp%'],
                ['lineOfBusiness*' => '%WC%'],
            ],
        ]);
    }
}
