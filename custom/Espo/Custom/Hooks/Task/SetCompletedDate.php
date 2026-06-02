<?php

namespace Espo\Custom\Hooks\Task;

use DateTimeImmutable;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Maintains Task.dateCompleted from status transitions (side-panel "Completed").
 *
 *  - status -> "Completed" with empty dateCompleted  => set dateCompleted = now
 *  - status -> away from "Completed"                  => clear dateCompleted
 *
 * Core EspoCRM does NOT auto-populate a custom dateCompleted field, so this hook
 * owns it. The field stays manually editable: if the user supplied a value in the
 * same save, we respect it and do not overwrite. Idempotent — re-saving a Task
 * that is already Completed (with dateCompleted set) makes no change.
 *
 * Auto-registers by path: Hooks/Task/SetCompletedDate.php.
 */
class SetCompletedDate implements BeforeSave
{
    private const COMPLETED = 'Completed';

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged('status')) {
            return;
        }

        $status = (string) ($entity->get('status') ?? '');
        $previous = $entity->isNew() ? null : $entity->getFetched('status');

        if ($status === self::COMPLETED && $previous !== self::COMPLETED) {
            // Entering Completed: stamp now unless a value was explicitly provided.
            if (!$entity->get('dateCompleted')) {
                $entity->set('dateCompleted', (new DateTimeImmutable())->format('Y-m-d H:i:s'));
            }

            return;
        }

        if ($status !== self::COMPLETED && $previous === self::COMPLETED) {
            // Leaving Completed (e.g. reopened): clear the completion stamp,
            // unless the user explicitly set a new value in this same save.
            if (!$entity->isAttributeChanged('dateCompleted')) {
                $entity->set('dateCompleted', null);
            }
        }
    }
}
