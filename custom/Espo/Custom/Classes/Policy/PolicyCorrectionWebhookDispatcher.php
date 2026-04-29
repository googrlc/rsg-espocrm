<?php

namespace Espo\Custom\Classes\Policy;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

class PolicyCorrectionWebhookDispatcher
{
    private const CORE_FIELDS = [
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

    public function __construct(
        private Config $config
    ) {}

    /**
     * @return array{dispatched: bool, changes: array<string, array{old: mixed, new: mixed}>}
     */
    public function dispatch(Entity $policy): array
    {
        $changes = $this->collectChanges($policy);
        if ($changes === []) {
            return ['dispatched' => false, 'changes' => []];
        }

        $webhookUrl = trim((string) ($this->config->get('policyCorrectionWebhookUrl') ?? ''));
        if ($webhookUrl === '') {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $payload = $this->buildPayload($policy, $changes);
        $body = json_encode($payload);
        if ($body === false) {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $headers = ['Content-Type: application/json'];
        $secret = trim((string) ($this->config->get('policyCorrectionWebhookSecret') ?? ''));
        if ($secret !== '') {
            $headers[] = 'X-Policy-Sync-Secret: ' . $secret;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($webhookUrl, false, $context);
        $statusCode = $this->extractStatusCode($http_response_header ?? []);
        $ok = $response !== false && $statusCode >= 200 && $statusCode < 300;

        return ['dispatched' => $ok, 'changes' => $changes];
    }

    /**
     * @param array<string, array{old: mixed, new: mixed}> $changes
     */
    private function buildPayload(Entity $policy, array $changes): array
    {
        return [
            'eventType' => 'policy.correction_submitted',
            'triggeredAt' => gmdate('c'),
            'policy' => [
                'id' => $policy->getId(),
                'momentumPolicyId' => (string) ($policy->get('momentumPolicyId') ?? ''),
                'policy_number' => (string) ($policy->get('policy_number') ?? ''),
                'accountId' => $policy->get('accountId'),
                'accountName' => (string) ($policy->get('accountName') ?? ''),
                'contactId' => $policy->get('contactId'),
                'contactName' => (string) ($policy->get('contactName') ?? ''),
                'modifiedAt' => (string) ($policy->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function collectChanges(Entity $policy): array
    {
        $changes = [];

        foreach (self::CORE_FIELDS as $field) {
            $oldValue = $this->normalizeValue($policy->getFetched($field));
            $newValue = $this->normalizeValue($policy->get($field));

            if ($oldValue === $newValue) {
                continue;
            }

            $changes[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $changes;
    }

    private function extractStatusCode(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d{3})/', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        return json_encode($value);
    }
}
