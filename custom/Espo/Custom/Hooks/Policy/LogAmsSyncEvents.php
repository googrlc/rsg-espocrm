<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Policy\PolicySyncAuditLogger;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class LogAmsSyncEvents implements AfterSave
{
    public function __construct(
        private PolicySyncAuditLogger $policySyncAuditLogger
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $this->logAcceptanceEvent($entity);
        $this->logLockStateTransitions($entity);
    }

    private function logAcceptanceEvent(Entity $entity): void
    {
        $acceptedAt = trim((string) ($entity->get('acceptedByAmsAt') ?? ''));
        $acceptedAtFetched = trim((string) ($entity->getFetched('acceptedByAmsAt') ?? ''));

        if ($acceptedAt === '' || $acceptedAt === $acceptedAtFetched) {
            return;
        }

        $this->policySyncAuditLogger->logDecision(
            $entity,
            'accepted',
            'AMS accepted CRM policy correction payload.',
            [
                'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                'targetTimestamp' => $acceptedAt,
            ]
        );
    }

    private function logLockStateTransitions(Entity $entity): void
    {
        $currentLockState = trim((string) ($entity->get('amsLockState') ?? ''));
        $previousLockState = trim((string) ($entity->getFetched('amsLockState') ?? ''));

        if ($currentLockState === $previousLockState) {
            return;
        }

        if ($currentLockState === 'Locked by AMS') {
            $this->policySyncAuditLogger->logDecision(
                $entity,
                'locked',
                'Policy core fields locked in CRM after AMS acceptance.',
                [
                    'targetTimestamp' => (string) ($entity->get('acceptedByAmsAt') ?? ''),
                ]
            );

            return;
        }

        if ($currentLockState === 'Rejected by AMS') {
            $reason = trim((string) ($entity->get('amsLockReason') ?? ''));
            if ($reason === '') {
                $reason = 'AMS rejected CRM correction payload.';
            }

            $this->policySyncAuditLogger->logDecision(
                $entity,
                'rejected',
                $reason,
                [
                    'targetTimestamp' => (string) ($entity->get('acceptedByAmsAt') ?? ''),
                ]
            );
        }
    }
}
