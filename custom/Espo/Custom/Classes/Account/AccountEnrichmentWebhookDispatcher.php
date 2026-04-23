<?php

namespace Espo\Custom\Classes\Account;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

class AccountEnrichmentWebhookDispatcher
{
    private const ENRICHMENT_FIELDS = [
        'dateOfBirth',
        'primaryDob',
        'fein',
        'primaryFirstName',
        'primaryLastName',
        'spouseFirstName',
        'spouseLastName',
        'spouseDob',
        'businessEntity',
        'sicCode',
        'intelNaics',
    ];

    public function __construct(
        private Config $config
    ) {}

    /**
     * @return array{dispatched: bool, changes: array<string, array{old: mixed, new: mixed}>}
     */
    public function dispatch(Entity $account): array
    {
        $changes = $this->collectChanges($account);
        if ($changes === []) {
            return ['dispatched' => false, 'changes' => []];
        }

        $webhookUrl = trim((string) ($this->config->get('accountEnrichmentWebhookUrl') ?? ''));
        if ($webhookUrl === '') {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $payload = [
            'eventType' => 'account.enrichment_submitted',
            'triggeredAt' => gmdate('c'),
            'account' => [
                'id' => $account->getId(),
                'momentumClientId' => (string) ($account->get('momentumClientId') ?? ''),
                'name' => (string) ($account->get('name') ?? ''),
                'modifiedAt' => (string) ($account->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];

        $body = json_encode($payload);
        if ($body === false) {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $headers = ['Content-Type: application/json'];
        $secret = trim((string) ($this->config->get('accountEnrichmentWebhookSecret') ?? ''));
        if ($secret !== '') {
            $headers[] = 'X-Account-Sync-Secret: ' . $secret;
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
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function collectChanges(Entity $account): array
    {
        $changes = [];

        foreach (self::ENRICHMENT_FIELDS as $field) {
            $oldValue = $this->normalize($account->getFetched($field));
            $newValue = $this->normalize($account->get($field));

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

    private function normalize(mixed $value): mixed
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
