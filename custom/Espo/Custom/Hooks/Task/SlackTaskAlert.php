<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Task\TaskSlackNotifier;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Fires Slack alerts on meaningful task events:
 *   - New task created
 *   - Status changed to Completed / Waiting on Client / Waiting on Carrier / Cancelled
 *   - Task reassigned to a different user
 */
class SlackTaskAlert implements AfterSave
{
    public function __construct(
        private TaskSlackNotifier $notifier
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $status = trim((string) ($entity->get('status') ?? ''));
        $prevStatus = trim((string) ($entity->getFetched('status') ?? ''));

        $assignedUserId = $entity->get('assignedUserId');
        $prevAssignedUserId = $entity->getFetched('assignedUserId');

        if ($entity->isNew()) {
            $this->notifier->notifyNewTask($entity);
            return;
        }

        if ($status !== $prevStatus && $status !== '' && $prevStatus !== '') {
            $this->notifier->notifyStatusChange($entity, $prevStatus, $status);
        }

        if ($assignedUserId !== $prevAssignedUserId && !empty($assignedUserId)) {
            $this->notifier->notifyReassignment($entity, $prevAssignedUserId, $assignedUserId);
        }
    }
}
