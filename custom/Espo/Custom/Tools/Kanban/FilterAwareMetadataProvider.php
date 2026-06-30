<?php
namespace Espo\Custom\Tools\Kanban;

use Espo\Core\Utils\Metadata;
use Espo\Tools\Kanban\MetadataProvider as BaseMetadataProvider;

/**
 * Makes the Kanban status-ignore list aware of the active primary filter.
 *
 * For Opportunities, the Closed Won / Closed Lost columns render only when the
 * Won or Lost primary filter is selected. On any other view (Open, Archived,
 * no filter) both closed columns stay hidden. Other entity types are unaffected.
 *
 * The active filter is pushed in by Espo\Custom\Tools\Kanban\KanbanService
 * right before the core Kanban tool computes its result.
 */
class FilterAwareMetadataProvider extends BaseMetadataProvider
{
    private static ?string $activePrimaryFilter = null;

    public function __construct(Metadata $metadata)
    {
        parent::__construct($metadata);
    }

    public static function setActivePrimaryFilter(?string $filter): void
    {
        self::$activePrimaryFilter = $filter;
    }

    public static function resetActivePrimaryFilter(): void
    {
        self::$activePrimaryFilter = null;
    }

    /**
     * @return string[]
     */
    public function getStatusIgnoreList(string $entityType): array
    {
        if ($entityType === 'Opportunity') {
            $statusList = $this->getStatusList($entityType);
            $filter = self::$activePrimaryFilter;

            // Won filter: render only the Closed Won column.
            if ($filter === 'won') {
                return array_values(
                    array_filter(
                        $statusList,
                        fn ($s) => $s !== 'Closed Won' && $s !== ''
                    )
                );
            }

            // Lost filter: render only the Closed Lost column.
            if ($filter === 'lost') {
                return array_values(
                    array_filter(
                        $statusList,
                        fn ($s) => $s !== 'Closed Lost' && $s !== ''
                    )
                );
            }

            // Archived filter: show every stage column so archived deals
            // (which can be in any stage) are all visible.
            if ($filter === 'archived') {
                return [];
            }
        }

        return parent::getStatusIgnoreList($entityType);
    }
}
