<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Exceptions\Forbidden;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Blocks closing a Task (status -> Completed) until every populated confirmation
 * checklist item is "Confirmed". Confirmation items live in fixed fields
 * confirm{1..4}Label / confirm{1..4}Status on the Task.
 */
class RequireConfirmationsBeforeClose implements BeforeSave
{
    private const SLOTS = 4;

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ((string) ($entity->get('status') ?? '') !== 'Completed') {
            return;
        }

        // Only enforce when status is actually transitioning to Completed.
        if (!$entity->isNew() && !$entity->isAttributeChanged('status')) {
            return;
        }

        $pending = 0;

        for ($i = 1; $i <= self::SLOTS; $i++) {
            $label = trim((string) ($entity->get("confirm{$i}Label") ?? ''));

            if ($label === '') {
                continue;
            }

            if ((string) ($entity->get("confirm{$i}Status") ?? '') !== 'Confirmed') {
                $pending++;
            }
        }

        if ($pending > 0) {
            throw new Forbidden(
                "Cannot complete this task: {$pending} confirmation item(s) are not yet Confirmed."
            );
        }
    }
}
