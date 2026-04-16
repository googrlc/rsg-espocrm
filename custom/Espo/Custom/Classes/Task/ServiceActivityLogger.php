<?php

namespace Espo\Custom\Classes\Task;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class ServiceActivityLogger
{
    private const STATUS_ACTIVITY_MAP = [
        'In Progress' => 'task_started',
        'Waiting on Client' => 'request_sent',
        'Waiting on Carrier' => 'carrier_waiting',
        'Completed' => 'request_completed',
    ];

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

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function log(Entity $task): void
    {
        $status = trim((string) ($task->get('status') ?? ''));
        $previousStatus = trim((string) ($task->getFetched('status') ?? ''));

        if ($status === $previousStatus) {
            return;
        }

        $activityKey = self::STATUS_ACTIVITY_MAP[$status] ?? null;
        if ($activityKey === null || !$this->isServiceTask($task)) {
            return;
        }

        $context = $this->resolveContext($task);
        if (empty($context['accountId'])) {
            return;
        }

        $activity = $this->entityManager->getNewEntity('ActivityLog');
        $activity->set([
            'name' => $this->buildName($task, $activityKey),
            'activityType' => $this->resolveActivityType($activityKey),
            'classification' => $this->resolveClassification($task),
            'dateTime' => $this->normalizeDateTimeForStorage(
                $task->get('modifiedAt') ?: gmdate('Y-m-d H:i:s')
            ),
            'notes' => $this->buildNotes($task, $activityKey),
            'loggedBy' => $this->resolveLoggedBy($task),
            'source' => 'n8n Automated',
            'accountId' => $context['accountId'],
            'accountName' => $context['accountName'],
            'contactId' => $context['contactId'],
            'contactName' => $context['contactName'],
            'policyId' => $context['policyId'],
            'policyName' => $context['policyName'],
            'assignedUserId' => $task->get('assignedUserId'),
            'assignedUserName' => $task->get('assignedUserName'),
            'teamsIds' => $task->get('teamsIds') ?? [],
        ]);

        $this->entityManager->saveEntity($activity);
    }

    private function isServiceTask(Entity $task): bool
    {
        $taskType = trim((string) ($task->get('taskType') ?? ''));

        return in_array($taskType, self::SERVICE_TASK_TYPES, true);
    }

    private function buildName(Entity $task, string $activityKey): string
    {
        $taskName = trim((string) ($task->get('name') ?? 'Service Request'));

        return match ($activityKey) {
            'task_started' => 'Service Task Started: ' . $taskName,
            'request_sent' => 'Service Request Sent: ' . $taskName,
            'carrier_waiting' => 'Waiting on Carrier: ' . $taskName,
            'request_completed' => 'Service Request Completed: ' . $taskName,
            default => 'Service Activity: ' . $taskName,
        };
    }

    private function buildNotes(Entity $task, string $activityKey): string
    {
        $taskType = trim((string) ($task->get('taskType') ?? 'Service'));
        $assignedUser = trim((string) ($task->get('assignedUserName') ?? ''));
        $triageSummary = trim((string) ($task->get('triageSummary') ?? ''));
        $triageReason = trim((string) ($task->get('triageReason') ?? ''));
        $description = trim((string) ($task->get('description') ?? ''));
        $dateDue = $this->normalizeDisplayDate($task->get('dateEnd') ?: $task->get('dateEndDate'));

        $lines = [];
        $lines[] = match ($activityKey) {
            'task_started' => 'Service work started from task status change.',
            'request_sent' => 'Service request sent to client from task status change.',
            'carrier_waiting' => 'Task moved to Waiting on Carrier from task status change.',
            'request_completed' => 'Completion acknowledgement sent to client from task status change.',
            default => 'Service lifecycle event triggered from task status change.',
        };
        $lines[] = 'Task: ' . trim((string) ($task->get('name') ?? ''));
        $lines[] = 'Task Type: ' . $taskType;

        if ($assignedUser !== '') {
            $lines[] = 'Owner: ' . $assignedUser;
        }

        if ($dateDue !== null) {
            $lines[] = 'Due: ' . $dateDue;
        }

        if ($triageSummary !== '') {
            $lines[] = 'Summary: ' . $triageSummary;
        }

        if ($triageReason !== '') {
            $lines[] = 'Reason: ' . $triageReason;
        }

        if ($description !== '') {
            $lines[] = 'Description: ' . $description;
        }

        return implode("\n", $lines);
    }

    private function resolveActivityType(string $activityKey): string
    {
        return match ($activityKey) {
            'task_started' => 'Note',
            'carrier_waiting' => 'Note',
            default => 'Email Out',
        };
    }

    private function resolveClassification(Entity $task): string
    {
        return match ((string) ($task->get('taskType') ?? '')) {
            'Claims' => 'Claim related',
            'Onboarding' => 'Onboarding',
            'Policy Change' => 'Coverage question',
            'Renewal' => 'Renewal inquiry',
            'New Business' => 'Quote request',
            'Commission' => 'Payment / billing',
            default => 'General correspondence',
        };
    }

    private function resolveLoggedBy(Entity $task): string
    {
        $modifiedByName = trim((string) ($task->get('modifiedByName') ?? ''));
        if ($modifiedByName !== '') {
            return $modifiedByName;
        }

        $assignedUserName = trim((string) ($task->get('assignedUserName') ?? ''));
        if ($assignedUserName !== '') {
            return $assignedUserName;
        }

        return 'CRM Service Workflow';
    }

    private function resolveContext(Entity $task): array
    {
        $accountId = $task->get('linkedAccountId') ?: $task->get('accountId');
        $accountName = (string) ($task->get('linkedAccountName') ?: $task->get('accountName') ?: '');
        $contactId = $task->get('contactId');
        $contactName = (string) ($task->get('contactName') ?? '');
        $policyId = null;
        $policyName = '';

        $parentType = (string) ($task->get('parentType') ?? '');
        $parentId = $task->get('parentId');

        if ($parentType === 'Account' && $parentId && !$accountId) {
            $accountId = $parentId;
            $accountName = (string) ($task->get('parentName') ?? $accountName);
        }

        if ($parentType === 'Contact' && $parentId) {
            $contactId = $contactId ?: $parentId;
            $contactName = $contactName !== '' ? $contactName : (string) ($task->get('parentName') ?? '');

            if (!$accountId) {
                $contact = $this->entityManager->getEntityById('Contact', $parentId);
                if ($contact) {
                    $accountId = $contact->get('accountId');
                    $accountName = (string) ($contact->get('accountName') ?? $accountName);
                }
            }
        }

        if ($parentType === 'Policy' && $parentId) {
            $policyId = $parentId;
            $policyName = (string) ($task->get('parentName') ?? '');

            if (!$accountId || !$contactId || $policyName === '') {
                $policy = $this->entityManager->getEntityById('Policy', $parentId);
                if ($policy) {
                    $accountId = $accountId ?: $policy->get('accountId');
                    $accountName = $accountName !== '' ? $accountName : (string) ($policy->get('accountName') ?? '');
                    $contactId = $contactId ?: $policy->get('contactId');
                    $contactName = $contactName !== '' ? $contactName : (string) ($policy->get('contactName') ?? '');
                    $policyName = $policyName !== '' ? $policyName : (string) ($policy->get('name') ?? '');
                }
            }
        }

        if ($parentType === 'Renewal' && $parentId) {
            $renewal = $this->entityManager->getEntityById('Renewal', $parentId);
            if ($renewal) {
                $accountId = $accountId ?: $renewal->get('accountId');
                $accountName = $accountName !== '' ? $accountName : (string) ($renewal->get('accountName') ?? '');
                $contactId = $contactId ?: $renewal->get('contactId');
                $contactName = $contactName !== '' ? $contactName : (string) ($renewal->get('contactName') ?? '');
                $policyId = $policyId ?: $renewal->get('policyId');
                $policyName = $policyName !== '' ? $policyName : (string) ($renewal->get('policyName') ?? '');
            }
        }

        return [
            'accountId' => $accountId,
            'accountName' => $accountName,
            'contactId' => $contactId,
            'contactName' => $contactName,
            'policyId' => $policyId,
            'policyName' => $policyName,
        ];
    }

    private function normalizeDateTimeForStorage(mixed $value): string
    {
        $stringValue = (string) $value;

        return substr(str_replace('T', ' ', $stringValue), 0, 19);
    }

    private function normalizeDisplayDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        return substr(str_replace('T', ' ', (string) $value), 0, 16);
    }
}
