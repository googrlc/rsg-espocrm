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

        $decisionLabel = strtoupper(str_replace('_', ' ', $decision));
        $policyNumber = trim((string) ($policy->get('policyNumber') ?? ''));

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

        $policyNumber = trim((string) ($policy->get('policyNumber') ?? ''));
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

        return implode("\n", $lines);
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
