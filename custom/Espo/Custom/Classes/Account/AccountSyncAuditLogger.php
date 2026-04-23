<?php

namespace Espo\Custom\Classes\Account;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AccountSyncAuditLogger
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function logDecision(Entity $account, string $decision, string $reason, array $context = []): void
    {
        $accountId = $account->getId();
        if (!$accountId) {
            return;
        }

        $activity = $this->entityManager->getNewEntity('ActivityLog');
        $activity->set([
            'name' => sprintf('Account Sync %s: %s', strtoupper($decision), (string) ($account->get('name') ?? $accountId)),
            'activityType' => 'n8n Automated',
            'classification' => 'General correspondence',
            'dateTime' => gmdate('Y-m-d H:i:s'),
            'notes' => $this->buildNotes($account, $decision, $reason, $context),
            'loggedBy' => 'CRM-AMS Account Sync',
            'source' => 'n8n Automated',
            'momentumTransactionId' => sprintf(
                'account-sync-%s-%s-%s',
                $accountId,
                $decision,
                substr(sha1(uniqid('', true)), 0, 12)
            ),
            'accountId' => $accountId,
            'accountName' => (string) ($account->get('name') ?? ''),
            'assignedUserId' => $account->get('assignedUserId'),
            'assignedUserName' => $account->get('assignedUserName'),
            'teamsIds' => $account->get('teamsIds') ?? [],
        ]);

        $this->entityManager->saveEntity($activity);
    }

    private function buildNotes(Entity $account, string $decision, string $reason, array $context): string
    {
        $lines = [];
        $lines[] = 'Decision: ' . $decision;
        $lines[] = 'Reason: ' . $reason;
        $lines[] = 'Account Id: ' . $account->getId();

        $momentumClientId = trim((string) ($account->get('momentumClientId') ?? ''));
        if ($momentumClientId !== '') {
            $lines[] = 'AMS Client Id: ' . $momentumClientId;
        }

        $sourceTimestamp = trim((string) ($context['sourceTimestamp'] ?? ''));
        if ($sourceTimestamp !== '') {
            $lines[] = 'Source Timestamp: ' . $sourceTimestamp;
        }

        $changedFields = $context['changedFields'] ?? [];
        if (is_array($changedFields) && $changedFields !== []) {
            $lines[] = 'Changed Fields: ' . implode(', ', $changedFields);
        }

        return implode("\n", $lines);
    }
}
