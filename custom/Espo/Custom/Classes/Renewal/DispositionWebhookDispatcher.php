<?php

namespace Espo\Custom\Classes\Renewal;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

class DispositionWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return ['disposition'];
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
            'eventType'    => 'renewal.disposition_changed',
            'triggeredAt'  => gmdate('c'),
            'renewal'      => [
                'id'             => $entity->getId(),
                'name'           => (string) ($entity->get('name') ?? ''),
                'disposition'    => (string) ($entity->get('disposition') ?? ''),
                'pipeline_stage' => (string) ($entity->get('pipeline_stage') ?? ''),
                'policyId'       => $entity->get('policyId'),
                'policyName'     => (string) ($entity->get('policyName') ?? ''),
                'accountId'      => $entity->get('accountId'),
                'accountName'    => (string) ($entity->get('accountName') ?? ''),
                'contactId'      => $entity->get('contactId'),
                'contactName'    => (string) ($entity->get('contactName') ?? ''),
                'assignedUserId' => $entity->get('assignedUserId'),
                'assignedUserName' => (string) ($entity->get('assignedUserName') ?? ''),
                'expiration_date'  => (string) ($entity->get('expiration_date') ?? ''),
                'current_premium'  => $entity->get('current_premium'),
                'renewal_premium'  => $entity->get('renewal_premium'),
                'line_of_business' => (string) ($entity->get('line_of_business') ?? ''),
                'carrier'          => (string) ($entity->get('carrier') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
