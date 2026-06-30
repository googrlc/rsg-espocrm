<?php
namespace Espo\Custom\Tools\Kanban;

use Espo\Core\Select\SearchParams;
use Espo\Tools\Kanban\KanbanService as BaseKanbanService;
use Espo\Tools\Kanban\Result;

/**
 * Pushes the active primary filter into the filter-aware metadata provider
 * before delegating to the core kanban data computation.
 */
class KanbanService extends BaseKanbanService
{
    public function getData(string $entityType, SearchParams $searchParams): Result
    {
        FilterAwareMetadataProvider::setActivePrimaryFilter($searchParams->getPrimaryFilter());

        try {
            return parent::getData($entityType, $searchParams);
        } finally {
            FilterAwareMetadataProvider::resetActivePrimaryFilter();
        }
    }
}
