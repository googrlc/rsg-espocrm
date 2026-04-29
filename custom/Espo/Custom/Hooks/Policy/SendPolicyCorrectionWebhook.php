<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Policy\PolicyCorrectionWebhookDispatcher;
use Espo\Custom\Classes\Policy\PolicySyncAuditLogger;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class SendPolicyCorrectionWebhook implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager,
        private PolicyCorrectionWebhookDispatcher $policyCorrectionWebhookDispatcher,
        private PolicySyncAuditLogger $policySyncAuditLogger
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $momentumPolicyId = trim((string) ($entity->get('momentumPolicyId') ?? ''));
        if ($momentumPolicyId === '') {
            return;
        }

        $lockState = trim((string) ($entity->get('amsLockState') ?? ''));
        if ($lockState === 'Locked by AMS') {
            return;
        }

        $dispatchResult = $this->policyCorrectionWebhookDispatcher->dispatch($entity);
        $changes = $dispatchResult['changes'] ?? [];
        if (!is_array($changes) || $changes === []) {
            return;
        }

        $changedFields = array_keys($changes);
        if ($dispatchResult['dispatched'] === true) {
            $this->markPendingAmsAcceptance($entity);
            $this->policySyncAuditLogger->logDecision(
                $entity,
                'pushed',
                'CRM correction payload sent to AMS for acceptance.',
                [
                    'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                    'changedFields' => $changedFields,
                ]
            );

            return;
        }

        $this->policySyncAuditLogger->logDecision(
            $entity,
            'skipped',
            'CRM correction payload had changes but did not dispatch to AMS (check webhook configuration/availability).',
            [
                'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                'changedFields' => $changedFields,
            ]
        );
    }

    private function markPendingAmsAcceptance(Entity $entity): void
    {
        if ((string) ($entity->get('amsLockState') ?? '') === 'Pending AMS') {
            return;
        }

        $entity->set([
            'amsLockState' => 'Pending AMS',
            'amsLockReason' => 'CRM correction submitted. Waiting for AMS acceptance timestamp.',
            'sync_status' => 'Pending',
        ]);

        $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
    }
}
