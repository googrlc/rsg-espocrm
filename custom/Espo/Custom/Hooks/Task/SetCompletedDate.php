<?php

namespace Espo\Custom\Hooks\Task;

use DateTimeImmutable;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Maintains Task.dateCompleted from status transitions (side-panel "Completed").
 *
 *  - status -> "Completed" with empty dateCompleted           => set dateCompleted = now
 *  - status -> a REOPENED active status (Inbox/In Progress/…) => clear dateCompleted
 *  - status -> a terminal status (Archived / Cancelled)       => KEEP dateCompleted
 *
 * Archiving or cancelling an already-completed task must preserve the completion
 * stamp — the task genuinely was completed; filing it away should not erase when.
 * This matters for the auto-archive sweep (ArchiveCompletedTasks), which moves
 * Completed -> Archived and relies on dateCompleted surviving the transition.
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
            // Only clear the completion stamp when the task is genuinely REOPENED
            // (moved back to an active status). Moving Completed -> a terminal status
            // such as "Archived" or "Cancelled" PRESERVES dateCompleted. Honour an
            // explicit value supplied in the same save either way.
            $activeStatusList = ['Inbox', 'In Progress', 'Waiting on Client', 'Waiting on Carrier'];

            if (in_array($status, $activeStatusList, true) && !$entity->isAttributeChanged('dateCompleted')) {
                $entity->set('dateCompleted', null);
            }
        }
    }
}
