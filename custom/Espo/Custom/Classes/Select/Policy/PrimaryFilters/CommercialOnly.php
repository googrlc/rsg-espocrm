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
                ['lineOfBusiness*' => '%auto%'],
                ['lineOfBusiness*' => '%liability%'],
                ['lineOfBusiness*' => '%workers%'],
                ['lineOfBusiness*' => '%comp%'],
                ['lineOfBusiness*' => '%commercial%'],
                ['lineOfBusiness*' => '%property%'],
                ['lineOfBusiness*' => '%BOP%'],
                ['lineOfBusiness*' => '%umbrella%'],
                ['lineOfBusiness*' => '%professional%'],
                ['lineOfBusiness*' => '%transport%'],
            ],
        ]);
    }
}
