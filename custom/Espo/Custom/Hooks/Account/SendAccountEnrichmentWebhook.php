<?php

namespace Espo\Custom\Hooks\Account;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Account\AccountEnrichmentWebhookDispatcher;
use Espo\Custom\Classes\Account\AccountSyncAuditLogger;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class SendAccountEnrichmentWebhook implements AfterSave
{
    public function __construct(
        private AccountEnrichmentWebhookDispatcher $accountEnrichmentWebhookDispatcher,
        private AccountSyncAuditLogger $accountSyncAuditLogger
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $momentumClientId = trim((string) ($entity->get('momentumClientId') ?? ''));
        if ($momentumClientId === '') {
            return;
        }

        $dispatchResult = $this->accountEnrichmentWebhookDispatcher->dispatch($entity);
        $changes = $dispatchResult['changes'] ?? [];
        if (!is_array($changes) || $changes === []) {
            return;
        }

        $changedFields = array_keys($changes);
        if ($dispatchResult['dispatched'] === true) {
            $this->accountSyncAuditLogger->logDecision(
                $entity,
                'pushed',
                'CRM account enrichment payload sent to AMS.',
                [
                    'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                    'changedFields' => $changedFields,
                ]
            );

            return;
        }

        $this->accountSyncAuditLogger->logDecision(
            $entity,
            'skipped',
            'CRM account enrichment payload had changes but did not dispatch to AMS (check webhook configuration/availability).',
            [
                'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                'changedFields' => $changedFields,
            ]
        );
    }
}
