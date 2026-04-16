<?php

namespace Espo\Custom\Classes\Task;

use Espo\ORM\Entity;

class ServiceLifecycleTaskChecker
{
    private const SERVICE_TASK_TYPES = [
        '',
        'Client Service',
        'Policy Change',
        'Claims',
        'Follow Up',
        'Onboarding',
        'Admin',
        'Other',
        'Renewal',
        'New Business',
        'Commission',
    ];

    public static function isEligible(Entity $task): bool
    {
        $taskType = trim((string) ($task->get('taskType') ?? ''));
        if (!in_array($taskType, self::SERVICE_TASK_TYPES, true)) {
            return false;
        }

        $automationKey = trim((string) ($task->get('automationKey') ?? ''));
        if (
            str_starts_with($automationKey, 'account-playbook:')
            || str_starts_with($automationKey, 'renewal:')
        ) {
            return false;
        }

        $taskName = trim((string) ($task->get('name') ?? ''));

        return !(
            $taskType === 'Renewal'
            && trim((string) ($task->get('parentType') ?? '')) === 'Renewal'
            && str_starts_with($taskName, 'Renewal Review:')
        );
    }
}
