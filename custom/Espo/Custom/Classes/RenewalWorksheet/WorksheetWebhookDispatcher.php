<?php

namespace Espo\Custom\Classes\RenewalWorksheet;

use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

class WorksheetWebhookDispatcher extends BaseWebhookDispatcher
{
    protected function getWatchedFields(): array
    {
        return ['state', 'completion_type'];
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
            'eventType'   => 'worksheet.completed',
            'triggeredAt' => gmdate('c'),
            'worksheet'   => [
                'id'              => $entity->getId(),
                'name'            => (string) ($entity->get('name') ?? ''),
                'state'           => (string) ($entity->get('state') ?? ''),
                'completion_type' => (string) ($entity->get('completion_type') ?? ''),
                'lob_variant'     => (string) ($entity->get('lob_variant') ?? ''),
                'renewalId'       => $entity->get('renewalId'),
                'renewalName'     => (string) ($entity->get('renewalName') ?? ''),
                'accountId'       => $entity->get('accountId'),
                'accountName'     => (string) ($entity->get('accountName') ?? ''),
                'contactId'       => $entity->get('contactId'),
                'contactName'     => (string) ($entity->get('contactName') ?? ''),
                'assignedUserId'  => $entity->get('assignedUserId'),
                'submitted_at'    => (string) ($entity->get('submitted_at') ?? ''),
                'line_of_business' => (string) ($entity->get('line_of_business') ?? ''),
                'carrier'         => (string) ($entity->get('carrier') ?? ''),
            ],
            'changes' => $changes,
        ];
    }
}
