<?php

namespace Espo\Custom\Classes\Task;

/**
 * Maps task urgency (workflow) to Task.priority (list/detail sort).
 */
class TaskPriorityMapper
{
    public static function fromUrgency(?string $urgency): string
    {
        $u = strtolower(trim((string) $urgency));

        return match ($u) {
            'urgent' => 'Urgent',
            'critical' => 'High',
            'high' => 'High',
            'normal' => 'Normal',
            'low' => 'Low',
            default => 'Normal',
        };
    }
}
