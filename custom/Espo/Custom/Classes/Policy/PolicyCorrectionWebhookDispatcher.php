<?php

namespace Espo\Custom\Classes\Policy;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

class PolicyCorrectionWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return [
            'status',
            'carrier',
            'line_of_business',
            'effective_date',
            'expiration_date',
            'premium_amount',
            'business_type',
            'bind_date',
            'billing_type',
            'policy_term',
            'cancellation_date',
            'reinstatement_date',
            'insuredMomentumId',
            'policy_notes',
        ];
    }

    protected function getWebhookUrlConfigKey(): string
    {
        return 'policyCorrectionWebhookUrl';
    }

    protected function getSecretConfigKey(): string
    {
        return 'policyCorrectionWebhookSecret';
    }

    protected function getSecretHeaderName(): string
    {
        return 'X-Policy-Sync-Secret';
    }

    protected function buildPayload(Entity $entity, array $changes): array
    {
        return [
            'eventType' => 'policy.correction_submitted',
            'triggeredAt' => gmdate('c'),
            'policy' => [
                'id' => $entity->getId(),
                'momentumPolicyId' => (string) ($entity->get('momentumPolicyId') ?? ''),
                'policy_number' => (string) ($entity->get('policy_number') ?? ''),
                'accountId' => $entity->get('accountId'),
                'accountName' => (string) ($entity->get('accountName') ?? ''),
                'contactId' => $entity->get('contactId'),
                'contactName' => (string) ($entity->get('contactName') ?? ''),
                'modifiedAt' => (string) ($entity->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
