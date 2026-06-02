<?php

namespace Espo\Custom\Classes\Account;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

class AccountEnrichmentWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return [
            'dateOfBirth',
            'primaryDob',
            'fein',
            'primaryFirstName',
            'primaryLastName',
            'businessEntity',
            'sic_code',
            'intel_naics',
        ];
    }

    protected function getWebhookUrlConfigKey(): string
    {
        return 'accountEnrichmentWebhookUrl';
    }

    protected function getSecretConfigKey(): string
    {
        return 'accountEnrichmentWebhookSecret';
    }

    protected function getSecretHeaderName(): string
    {
        return 'X-Account-Sync-Secret';
    }

    protected function buildPayload(Entity $entity, array $changes): array
    {
        return [
            'eventType' => 'account.enrichment_submitted',
            'triggeredAt' => gmdate('c'),
            'account' => [
                'id' => $entity->getId(),
                'momentumClientId' => (string) ($entity->get('momentum_client_id') ?? ''),
                'name' => (string) ($entity->get('name') ?? ''),
                'modifiedAt' => (string) ($entity->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
