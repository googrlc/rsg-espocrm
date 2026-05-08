<?php

namespace Espo\Custom\Classes\Policy;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class PolicySyncAuditLogger
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function logDecision(Entity $policy, string $decision, string $reason, array $context = []): void
    {
        $accountId = trim((string) ($policy->get('accountId') ?? ''));
        if ($accountId === '') {
            return;
        }

        $syncMeta = $this->buildSyncMeta($policy, $decision, $context);
        $context = array_merge($context, [
            'syncDirection' => $syncMeta['syncDirection'],
            'syncCompletionStatus' => $syncMeta['syncCompletionStatus'],
            'syncNeedsAttention' => $syncMeta['syncNeedsAttention'],
            'syncRunId' => $syncMeta['syncRunId'],
        ]);

        $decisionLabel = strtoupper(str_replace('_', ' ', $decision));
        $policyNumber = trim((string) ($policy->get('policy_number') ?? ''));

        $activity = $this->entityManager->getNewEntity('ActivityLog');
        $activity->set([
            'name' => sprintf(
                'AMS Sync %s: %s',
                $decisionLabel,
                $policyNumber !== '' ? $policyNumber : $policy->getId()
            ),
            'activityType' => 'n8n Automated',
            'classification' => 'General correspondence',
            'dateTime' => gmdate('Y-m-d H:i:s'),
            'notes' => $this->buildNotes($policy, $decision, $reason, $context),
            'loggedBy' => 'CRM-AMS Sync Governance',
            'source' => 'n8n Automated',
            'syncDirection' => $syncMeta['syncDirection'],
            'syncCompletionStatus' => $syncMeta['syncCompletionStatus'],
            'syncNeedsAttention' => $syncMeta['syncNeedsAttention'],
            'syncRunId' => $syncMeta['syncRunId'],
            'momentumTransactionId' => $this->buildTransactionId($policy, $decision),
            'accountId' => $policy->get('accountId'),
            'accountName' => $policy->get('accountName'),
            'contactId' => $policy->get('contactId'),
            'contactName' => $policy->get('contactName'),
            'policyId' => $policy->getId(),
            'policyName' => (string) ($policy->get('name') ?? ''),
            'assignedUserId' => $policy->get('assignedUserId'),
            'assignedUserName' => $policy->get('assignedUserName'),
            'teamsIds' => $policy->get('teamsIds') ?? [],
        ]);

        $this->entityManager->saveEntity($activity);
    }

    private function buildNotes(Entity $policy, string $decision, string $reason, array $context): string
    {
        $lines = [];
        $lines[] = 'Decision: ' . $decision;
        $lines[] = 'Reason: ' . $reason;
        $lines[] = 'Policy Id: ' . $policy->getId();

        $policyNumber = trim((string) ($policy->get('policy_number') ?? ''));
        if ($policyNumber !== '') {
            $lines[] = 'Policy Number: ' . $policyNumber;
        }

        $momentumPolicyId = trim((string) ($policy->get('momentumPolicyId') ?? ''));
        if ($momentumPolicyId !== '') {
            $lines[] = 'AMS Policy Id: ' . $momentumPolicyId;
        }

        $sourceTimestamp = trim((string) ($context['sourceTimestamp'] ?? ''));
        if ($sourceTimestamp !== '') {
            $lines[] = 'Source Timestamp: ' . $sourceTimestamp;
        }

        $targetTimestamp = trim((string) ($context['targetTimestamp'] ?? ''));
        if ($targetTimestamp !== '') {
            $lines[] = 'Target Timestamp: ' . $targetTimestamp;
        }

        $acceptedByAmsAt = trim((string) ($policy->get('acceptedByAmsAt') ?? ''));
        if ($acceptedByAmsAt !== '') {
            $lines[] = 'AMS Accepted At: ' . $acceptedByAmsAt;
        }

        $changedFields = $context['changedFields'] ?? [];
        if (is_array($changedFields) && $changedFields !== []) {
            $lines[] = 'Changed Fields: ' . implode(', ', $changedFields);
        }

        $syncDirection = trim((string) ($context['syncDirection'] ?? ''));
        if ($syncDirection !== '') {
            $lines[] = 'Sync Direction: ' . $syncDirection;
        }

        $syncCompletionStatus = trim((string) ($context['syncCompletionStatus'] ?? ''));
        if ($syncCompletionStatus !== '') {
            $lines[] = 'Sync Completion Status: ' . $syncCompletionStatus;
        }

        $syncNeedsAttention = $context['syncNeedsAttention'] ?? null;
        if ($syncNeedsAttention !== null) {
            $lines[] = 'Needs Attention: ' . ((bool) $syncNeedsAttention ? 'Yes' : 'No');
        }

        $syncRunId = trim((string) ($context['syncRunId'] ?? ''));
        if ($syncRunId !== '') {
            $lines[] = 'Sync Run Id: ' . $syncRunId;
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $context
     * @return array{syncDirection: string, syncCompletionStatus: string, syncNeedsAttention: bool, syncRunId: string}
     */
    private function buildSyncMeta(Entity $policy, string $decision, array $context): array
    {
        $syncDirection = trim((string) ($context['syncDirection'] ?? $this->inferSyncDirection($decision)));
        $syncCompletionStatus = trim((string) ($context['syncCompletionStatus'] ?? $this->inferSyncCompletionStatus($decision)));

        $syncNeedsAttention = array_key_exists('syncNeedsAttention', $context)
            ? (bool) $context['syncNeedsAttention']
            : $syncCompletionStatus !== 'Completed';

        $syncRunId = trim((string) ($context['syncRunId'] ?? ''));
        if ($syncRunId === '') {
            $syncRunId = sprintf(
                'policy-sync-%s-%s',
                $policy->getId(),
                gmdate('YmdHis')
            );
        }

        return [
            'syncDirection' => $syncDirection,
            'syncCompletionStatus' => $syncCompletionStatus,
            'syncNeedsAttention' => $syncNeedsAttention,
            'syncRunId' => $syncRunId,
        ];
    }

    private function inferSyncDirection(string $decision): string
    {
        return match ($decision) {
            'accepted', 'rejected', 'locked' => 'Inbound AMS -> CRM',
            default => 'Outbound CRM -> AMS',
        };
    }

    private function inferSyncCompletionStatus(string $decision): string
    {
        return match ($decision) {
            'pushed' => 'Pending',
            'accepted', 'locked' => 'Completed',
            'rejected', 'blocked', 'skipped' => 'Failed',
            default => 'Completed',
        };
    }

    private function buildTransactionId(Entity $policy, string $decision): string
    {
        return sprintf(
            'policy-sync-%s-%s-%s',
            $policy->getId(),
            $decision,
            substr(sha1(uniqid('', true)), 0, 12)
        );
    }
}
