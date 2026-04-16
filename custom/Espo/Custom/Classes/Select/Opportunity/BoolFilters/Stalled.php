<?php
namespace Espo\Custom\Classes\Select\Opportunity\BoolFilters;

use DateTimeImmutable;
use Espo\Core\Select\Bool\Filter;
use Espo\ORM\Query\SelectBuilder;

class Stalled implements Filter
{
    /** Stages that are not "in pipeline" — stalled only applies to open opportunities */
    private const TERMINAL_STAGES = [
        'Closed Won',
        'Closed Lost',
        'Bound / Renewed',
        'Non-Renewal / Lost',
        '',
    ];

    public function apply(SelectBuilder $queryBuilder, string $boolFilterName): void
    {
        $cutoff = (new DateTimeImmutable('-14 days'))->format('Y-m-d H:i:s');

        $queryBuilder->where([
            'modifiedAt<' => $cutoff,
            'stage!=' => self::TERMINAL_STAGES,
        ]);
    }
}
