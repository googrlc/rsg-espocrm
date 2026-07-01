<?php

namespace Espo\Custom\Classes\Renewal;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

/**
 * Renewal Loop v6 — fires ``renewal.disposition_changed`` to Hermes
 * (POST /webhooks/espo/disposition) when disposition / pipeline_stage changes.
 * Auth: shared serviceWebhookSecret via X-Service-Webhook-Secret header.
 */
class RenewalDispositionWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return ['disposition', 'pipeline_stage'];
    }

    protected function getWebhookUrlConfigKey(): string
    {
        return 'renewalDispositionWebhookUrl';
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
            'eventType' => 'renewal.disposition_changed',
            'triggeredAt' => gmdate('c'),
            'renewal' => [
                'id' => $entity->getId(),
                'name' => (string) ($entity->get('name') ?? ''),
                'pipeline_stage' => (string) ($entity->get('pipeline_stage') ?? ''),
                'disposition' => (string) ($entity->get('disposition') ?? ''),
                'ams_policy_guid' => (string) ($entity->get('ams_policy_guid') ?? ''),
                'ams_insured_guid' => (string) ($entity->get('ams_insured_guid') ?? ''),
                'line_of_business' => (string) ($entity->get('line_of_business') ?? ''),
                'account_id' => (string) ($entity->get('accountId') ?? ''),
                'account_name' => (string) ($entity->get('accountName') ?? ''),
                'modified_at' => (string) ($entity->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
