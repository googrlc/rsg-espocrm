<?php

namespace Espo\Custom\Classes\Webhook;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

abstract class BaseWebhookDispatcher
{
    public function __construct(
        private Config $config
    ) {}

    /** @return string[] */
    abstract protected function getWatchedFields(): array;

    abstract protected function getWebhookUrlConfigKey(): string;

    abstract protected function getSecretConfigKey(): string;

    abstract protected function getSecretHeaderName(): string;

    /**
     * @param array<string, array{old: mixed, new: mixed}> $changes
     */
    abstract protected function buildPayload(Entity $entity, array $changes): array;

    /**
     * @return array{dispatched: bool, changes: array<string, array{old: mixed, new: mixed}>}
     */
    public function dispatch(Entity $entity): array
    {
        $changes = $this->collectChanges($entity);
        if ($changes === []) {
            return ['dispatched' => false, 'changes' => []];
        }

        $webhookUrl = trim((string) ($this->config->get($this->getWebhookUrlConfigKey()) ?? ''));
        if ($webhookUrl === '') {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $body = json_encode($this->buildPayload($entity, $changes));
        if ($body === false) {
            return ['dispatched' => false, 'changes' => $changes];
        }

        $headers = ['Content-Type: application/json'];
        $secret = trim((string) ($this->config->get($this->getSecretConfigKey()) ?? ''));
        if ($secret !== '') {
            $headers[] = $this->getSecretHeaderName() . ': ' . $secret;
        }

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $ok = $response !== false && $statusCode >= 200 && $statusCode < 300;

        return ['dispatched' => $ok, 'changes' => $changes];
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function collectChanges(Entity $entity): array
    {
        $changes = [];

        foreach ($this->getWatchedFields() as $field) {
            if (!$entity->hasAttribute($field)) {
                continue;
            }

            $oldValue = $this->normalizeValue($entity->getFetched($field));
            $newValue = $this->normalizeValue($entity->get($field));

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
