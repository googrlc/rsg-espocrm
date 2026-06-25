<?php

namespace Espo\Custom\Classes\Task;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Posts task alert messages as Slack DMs via the chat.postMessage API.
 *
 * Each assignee gets a direct message — no channel posts.
 * Unassigned tasks fall back to Lamar.
 */
class TaskSlackNotifier
{
    /** EspoCRM user IDs */
    private const GRETCHEN_CRM_ID = '69bdf81552aaa';
    private const GENERAL_CRM_ID  = '6a3ae3d951b79b796';

    private const ALERTABLE_STATUSES = [
        'Completed',
        'Waiting on Client',
        'Waiting on Carrier',
        'Cancelled',
    ];

    public function __construct(
        private Config $config,
        private EntityManager $entityManager
    ) {}

    public function notifyNewTask(Entity $task): void
    {
        $this->send(
            $task,
            ':spiral_calendar_pad: New Task Assigned',
            $this->buildFields($task)
        );
    }

    public function notifyStatusChange(Entity $task, string $oldStatus, string $newStatus): void
    {
        if (!in_array($newStatus, self::ALERTABLE_STATUSES, true)) {
            return;
        }

        $emoji  = $this->statusEmoji($newStatus);
        $title  = $emoji . ' Task ' . $newStatus;
        $fields = $this->buildFields($task);
        $fields['*Previous:*'] = $oldStatus ?: '(none)';

        $this->send($task, $title, $fields);
    }

    public function notifyReassignment(Entity $task, ?string $oldAssigneeId, ?string $newAssigneeId): void
    {
        $oldName = $this->resolveUserName($oldAssigneeId) ?: 'Unassigned';
        $newName = $this->resolveUserName($newAssigneeId) ?: 'Unassigned';

        $fields = $this->buildFields($task);
        $fields['*Previously Assigned:*'] = $oldName;

        $this->send(
            $task,
            ':arrows_counterclockwise: Task Reassigned to You',
            $fields
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Message building
    // ─────────────────────────────────────────────────────────────

    /**
     * @return array<string,string>
     */
    private function buildFields(Entity $task): array
    {
        $fields = [
            '*Task:*'      => $this->buildTaskLink($task),
            '*Client:*'    => $task->get('accountName') ?: $task->get('contactName') ?: 'N/A',
            '*Status:*'    => $task->get('status') ?: 'N/A',
            '*Type:*'      => $task->get('taskType') ?: 'N/A',
            '*Priority:*'  => $task->get('priority') ?: 'N/A',
            '*Assigned To:*'=> $this->resolveUserName($task->get('assignedUserId')) ?: 'Unassigned',
            '*Due:*'       => $this->formatDate($task->get('dateEnd') ?: $task->get('dateEndDate')),
        ];

        $urgency = $task->get('urgency');
        if ($urgency && $urgency !== 'Normal') {
            $fields['*Urgency:*'] = $urgency;
        }

        return $fields;
    }

    private function buildTaskLink(Entity $task): string
    {
        $url  = rtrim((string) $this->config->get('siteUrl'), '/') . '/#Task/view/' . $task->getId();
        $name = $task->get('name') ?: 'Untitled Task';

        return "<{$url}|{$name}>";
    }

    // ─────────────────────────────────────────────────────────────
    // DM routing — maps EspoCRM user → Slack member ID
    // ─────────────────────────────────────────────────────────────

    /**
     * Resolve the Slack member ID for the task's assignee.
     * Unassigned tasks fall back to Lamar.
     */
    private function resolveSlackChannel(Entity $task): string
    {
        $assigneeId = $task->get('assignedUserId');

        if ($assigneeId === self::GRETCHEN_CRM_ID) {
            return trim((string) ($this->config->get('taskSlackUserIdGretchen') ?? ''));
        }

        // Lamar or unassigned → Lamar
        return trim((string) ($this->config->get('taskSlackUserIdLamar') ?? ''));
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function resolveUserName(?string $userId): string
    {
        if (!$userId) {
            return '';
        }

        $user = $this->entityManager->getEntityById('User', $userId);
        if (!$user) {
            return '';
        }

        $name = trim((string) ($user->get('firstName') . ' ' . $user->get('lastName')));
        return $name !== '' ? $name : (string) ($user->get('userName') ?: '');
    }

    private function formatDate(mixed $value): string
    {
        if (!$value) {
            return 'N/A';
        }

        $str = (string) $value;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return date('M j, Y', strtotime($str));
        }
        $ts = strtotime($str);
        return $ts ? date('M j, Y g:i A', $ts) : $str;
    }

    private function statusEmoji(string $status): string
    {
        return match ($status) {
            'Completed'          => ':white_check_mark:',
            'Waiting on Client'  => ':hourglass_flowing_sand:',
            'Waiting on Carrier' => ':hourglass_flowing_sand:',
            'Cancelled'          => ':x:',
            default              => ':bell:',
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Slack API — chat.postMessage with bot token
    // ─────────────────────────────────────────────────────────────

    /**
     * @param array<string,string> $fields
     */
    private function send(Entity $task, string $title, array $fields): void
    {
        $botToken = trim((string) ($this->config->get('taskSlackBotToken') ?? ''));

        if ($botToken === '') {
            error_log('[TaskSlackNotifier] taskSlackBotToken not configured — skipping alert.');
            return;
        }

        $channel = $this->resolveSlackChannel($task);

        if ($channel === '') {
            error_log('[TaskSlackNotifier] No Slack member ID configured for assignee — skipping alert.');
            return;
        }

        $lines = [];
        foreach ($fields as $label => $value) {
            $lines[] = "{$label} {$value}";
        }

        $blocks = [
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => $title, 'emoji' => true],
            ],
            [
                'type' => 'section',
                'text' => ['type' => 'mrkdwn', 'text' => implode("\n", $lines)],
            ],
            ['type' => 'divider'],
        ];

        $payload = json_encode([
            'channel' => $channel,
            'text'    => $title . ' — ' . ($task->get('name') ?: 'Task'),
            'blocks'  => $blocks,
        ]);

        $ch = curl_init('https://slack.com/api/chat.postMessage');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $botToken,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("[TaskSlackNotifier] Slack API HTTP {$httpCode} — {$response}");
            return;
        }

        // chat.postMessage returns 200 even on error — check the ok field
        $result = json_decode((string) $response, true);
        if (!($result['ok'] ?? false)) {
            $error = $result['error'] ?? 'unknown';
            error_log("[TaskSlackNotifier] Slack API error: {$error}");
        }
    }
}
