<?php

namespace Espo\Custom\Classes\Policy;

use Espo\Core\Utils\Config;
use Espo\Custom\Classes\Webhook\BaseWebhookDispatcher;
use Espo\ORM\Entity;

/**
 * Pushes a Policy lifecycle change to the standalone RSG Commission Tracker
 * (commissions engine):
 *
 *  - a policy in a *closed/won* status (default: Active) feeds expected
 *    commission — the engine computes it from its own carrier rulebook;
 *  - a policy in a *cancel* status (default: Cancelled, Flat Cancel, Lapsed)
 *    carries `voided: true`, so the engine reverses the expected commission.
 *
 * Expired / Non-Renewed are intentionally *not* cancel statuses — they earned
 * their full term commission, so they are left as-is.
 *
 * The engine owns the calculation; we only send the raw inputs it needs to
 * match a rule (carrier, line of business, new/renewal, premium) plus identity
 * fields for idempotent upserts (`crmPolicyId`).
 */
class PolicyClosedWebhookDispatcher extends BaseWebhookDispatcher
{
    /** @var string[] */
    private const DEFAULT_CLOSED_STATUSES = ['Active'];

    /** @var string[] */
    private const DEFAULT_CANCEL_STATUSES = ['Cancelled', 'Flat Cancel', 'Lapsed'];

    public function __construct(
        private Config $config
    ) {
        parent::__construct($config);
    }

    /** A closed/won status feeds expected commission. */
    public function isClosedStatus(string $status): bool
    {
        return in_array(
            trim($status),
            $this->getStatusList('commissionEngineClosedStatusList', self::DEFAULT_CLOSED_STATUSES),
            true
        );
    }

    /** A cancel status reverses (voids) expected commission. */
    public function isVoidStatus(string $status): bool
    {
        return in_array(
            trim($status),
            $this->getStatusList('commissionEngineCancelStatusList', self::DEFAULT_CANCEL_STATUSES),
            true
        );
    }

    /** Statuses the bridge reacts to at all. */
    public function isRelevantStatus(string $status): bool
    {
        return $this->isClosedStatus($status) || $this->isVoidStatus($status);
    }

    protected function getWatchedFields(): array
    {
        // Re-push on the initial activation, on later AMS-driven corrections to
        // the policy's financial/identity fields, and on cancellation. The hook
        // gates on status; the base dispatcher's change detection means we only
        // POST when one of these actually changed.
        return [
            'status',
            'policy_number',
            'carrier',
            'line_of_business',
            'business_type',
            'premium_amount',
            'commission_rate',
            'effective_date',
            'expiration_date',
            'bind_date',
            'cancellation_date',
        ];
    }

    protected function getWebhookUrlConfigKey(): string
    {
        return 'commissionEngineWebhookUrl';
    }

    protected function getSecretConfigKey(): string
    {
        return 'commissionEngineWebhookSecret';
    }

    protected function getSecretHeaderName(): string
    {
        return 'X-Commission-Sync-Secret';
    }

    protected function buildPayload(Entity $entity, array $changes): array
    {
        $status = trim((string) ($entity->get('status') ?? ''));
        $voided = $this->isVoidStatus($status);

        $bindDate = $this->toDateOrNull($entity->get('bind_date'));
        $effectiveDate = $this->toDateOrNull($entity->get('effective_date'));

        return [
            'eventType' => $voided ? 'policy.cancelled' : 'policy.closed',
            'triggeredAt' => gmdate('c'),
            // Engine `WonPolicy` inputs. Field names mirror the engine's schema
            // so its ingestion endpoint can map them with no translation table.
            'policy' => [
                'crmPolicyId' => (string) ($entity->getId() ?? ''),
                'momentumPolicyId' => (string) ($entity->get('momentumPolicyId') ?? ''),
                'policyNumber' => (string) ($entity->get('policy_number') ?? ''),
                'clientName' => (string) ($entity->get('accountName') ?? ''),
                'accountId' => $entity->get('accountId'),
                'carrier' => (string) ($entity->get('carrier') ?? ''),
                'lineOfBusiness' => (string) ($entity->get('line_of_business') ?? ''),
                'newRenewal' => $this->mapNewRenewal((string) ($entity->get('business_type') ?? '')),
                'premiumAmount' => $this->toFloatOrNull($entity->get('premium_amount')),
                'commissionRate' => $this->toFloatOrNull($entity->get('commission_rate')),
                'status' => $status,
                'voided' => $voided,
                'dateWon' => $bindDate ?? $effectiveDate,
                'effectiveDate' => $effectiveDate,
                'expirationDate' => $this->toDateOrNull($entity->get('expiration_date')),
                'cancellationDate' => $this->toDateOrNull($entity->get('cancellation_date')),
                'cancellationReason' => (string) ($entity->get('cancellation_reason') ?? ''),
                'modifiedAt' => (string) ($entity->get('modifiedAt') ?? ''),
            ],
            'changes' => $changes,
        ];
    }

    /**
     * @param string[] $default
     * @return string[]
     */
    private function getStatusList(string $configKey, array $default): array
    {
        $configured = $this->config->get($configKey);

        if (is_array($configured) && $configured !== []) {
            return array_values(array_filter(array_map(
                static fn ($value): string => trim((string) $value),
                $configured
            )));
        }

        return $default;
    }

    private function mapNewRenewal(string $businessType): string
    {
        return str_contains(strtolower(trim($businessType)), 'renew') ? 'Renewal' : 'New';
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function toDateOrNull(mixed $value): ?string
    {
        $date = trim((string) ($value ?? ''));

        return $date !== '' ? substr($date, 0, 10) : null;
    }
}
