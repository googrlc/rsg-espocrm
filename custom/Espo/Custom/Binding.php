<?php
namespace Espo\Custom;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingProcessor;

/**
 * Custom dependency bindings for RSG.
 */
class Binding implements BindingProcessor
{
    public function process(Binder $binder): void
    {
        $binder->bindImplementation(
            'Espo\\Tools\\Kanban\\KanbanService',
            'Espo\\Custom\\Tools\\Kanban\\KanbanService'
        );

        $binder->bindImplementation(
            'Espo\\Tools\\Kanban\\MetadataProvider',
            'Espo\\Custom\\Tools\\Kanban\\FilterAwareMetadataProvider'
        );
    }
}
