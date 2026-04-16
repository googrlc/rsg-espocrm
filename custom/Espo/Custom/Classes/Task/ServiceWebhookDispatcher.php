<?php

namespace Espo\Custom\Classes\Task;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

class ServiceWebhookDispatcher
{
    private const STATUS_EVENT_MAP = [
        'In Progress' => 'service.task_started',
        'Waiting on Client' => 'service.request_to_client',
        'Completed' => 'service.task_completed',
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
    ];

    public function __construct(
        private Config $config
    ) {}

    public function dispatch(Entity $task): void
    {
        $status = trim((string) ($task->get('status') ?? ''));
        $previousStatus = trim((string) ($task->getFetched('status') ?? ''));

        if ($status === $previousStatus) {
            return;
        }

        $eventType = self::STATUS_EVENT_MAP[$status] ?? null;
        if ($eventType === null || !$this->isServiceTask($task)) {
            return;
        }

        $webhookUrl = $this->resolveWebhookUrl($status);
        if ($webhookUrl === '') {
            return;
        }

        $payload = $this->buildPayload($task, $eventType, $previousStatus);
        $this->send($webhookUrl, $eventType, $payload);
    }

    private function isServiceTask(Entity $task): bool
    {
        $taskType = trim((string) ($task->get('taskType') ?? ''));

        return in_array($taskType, self::SERVICE_TASK_TYPES, true);
    }

    private function resolveWebhookUrl(string $status): string
    {
        $statusSpecificKey = match ($status) {
            'In Progress' => 'serviceStartedWebhookUrl',
            'Waiting on Client' => 'serviceRequestWebhookUrl',
            'Completed' => 'serviceCompletionWebhookUrl',
            default => null,
        };

        $url = $statusSpecificKey ? $this->config->get($statusSpecificKey) : null;
        if (!empty($url)) {
            return trim((string) $url);
        }

        return trim((string) ($this->config->get('serviceWebhookUrl') ?? ''));
    }

    private function buildPayload(Entity $task, string $eventType, string $previousStatus): array
    {
        $completedAt = $this->normalizeDateTime(
            $task->get('dateCompleted')
            ?: ($task->get('modifiedAt') ?? null)
        );

        $payload = [
            'eventType' => $eventType,
            'triggeredAt' => gmdate('c'),
            'task' => [
                'id' => $task->getId(),
                'name' => (string) ($task->get('name') ?? ''),
                'status' => (string) ($task->get('status') ?? ''),
                'previousStatus' => $previousStatus,
                'taskType' => (string) ($task->get('taskType') ?? ''),
                'urgency' => (string) ($task->get('urgency') ?? ''),
                'priority' => (string) ($task->get('priority') ?? ''),
                'description' => (string) ($task->get('description') ?? ''),
                'triageSummary' => (string) ($task->get('triageSummary') ?? ''),
                'triageReason' => (string) ($task->get('triageReason') ?? ''),
                'syncSource' => (string) ($task->get('syncSource') ?? ''),
                'taskSource' => (string) ($task->get('taskSource') ?? ''),
                'assignedUserId' => $task->get('assignedUserId'),
                'assignedUserName' => (string) ($task->get('assignedUserName') ?? ''),
                'linkedAccountId' => $task->get('linkedAccountId'),
                'linkedAccountName' => (string) ($task->get('linkedAccountName') ?? ''),
                'accountId' => $task->get('accountId'),
                'accountName' => (string) ($task->get('accountName') ?? ''),
                'contactId' => $task->get('contactId'),
                'contactName' => (string) ($task->get('contactName') ?? ''),
                'parentType' => (string) ($task->get('parentType') ?? ''),
                'parentId' => $task->get('parentId'),
                'parentName' => (string) ($task->get('parentName') ?? ''),
                'dateStart' => $this->normalizeDateTime($task->get('dateStart') ?: $task->get('dateStartDate')),
                'dateDue' => $this->normalizeDateTime($task->get('dateEnd') ?: $task->get('dateEndDate')),
                'dateCompleted' => $completedAt,
            ],
        ];

        if ($eventType === 'service.request_to_client') {
            $payload['request'] = [
                'message' => 'Task moved to Waiting on Client in EspoCRM.',
                'requestedAt' => $this->normalizeDateTime($task->get('modifiedAt')) ?? gmdate('c'),
            ];
        }

        if ($eventType === 'service.task_started') {
            $payload['started'] = [
                'message' => 'Task moved to In Progress in EspoCRM.',
                'startedAt' => $this->normalizeDateTime($task->get('modifiedAt')) ?? gmdate('c'),
            ];
        }

        if ($eventType === 'service.task_completed') {
            $payload['acknowledgement'] = [
                'message' => 'Task marked completed in EspoCRM.',
                'completedAt' => $completedAt ?? gmdate('c'),
            ];
        }

        return $payload;
    }

    private function send(string $webhookUrl, string $eventType, array $payload): void
    {
        $body = json_encode($payload);
        if ($body === false) {
            return;
        }

        $headers = [
            'Content-Type: application/json',
            'X-Service-Webhook-Event: ' . $eventType,
        ];

        $secret = trim((string) ($this->config->get('serviceWebhookSecret') ?? ''));
        if ($secret !== '') {
            $headers[] = 'X-Service-Webhook-Secret: ' . $secret;
        }

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        $stringValue = (string) $value;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $stringValue) === 1) {
            return $stringValue;
        }

        return str_replace(' ', 'T', substr($stringValue, 0, 19));
    }
}
