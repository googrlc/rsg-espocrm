<?php

namespace Espo\Custom\Classes\RenewalWorksheet;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

/**
 * Renewal Loop v6 — fires ``worksheet.completed`` to Hermes
 * (POST /webhooks/espo/worksheet) when a worksheet reaches state=completed.
 * Supabase ledger only (no Momentum MCP writeback on this event).
 * Auth: shared serviceWebhookSecret via X-Service-Webhook-Secret header.
 */
class WorksheetCompletedWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return ['state'];
    }

    protected function getWebhookUrlConfigKey(): string
    {
        return 'worksheetCompletedWebhookUrl';
    }

    protected function getSecretConfigKey(): string
    {
        return 'serviceWebhookSecret';
    }

    protected function getSecretHeaderName(): string
    {
        return 'X-Service-Webhook-Secret';
    }

    protected function buildPayload(Entity $entity, array $changes): array
    {
        return [
            'eventType' => 'worksheet.completed',
            'triggeredAt' => gmdate('c'),
            'worksheet' => [
                'id' => $entity->getId(),
                'name' => (string) ($entity->get('name') ?? ''),
                'lob_variant' => (string) ($entity->get('lob_variant') ?? ''),
                'completion_type' => (string) ($entity->get('completion_type') ?? ''),
                'state' => (string) ($entity->get('state') ?? ''),
                'renewal_id' => (string) ($entity->get('renewalId') ?? ''),
                'account_id' => (string) ($entity->get('accountId') ?? ''),
                'modified_at' => (string) ($entity->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
