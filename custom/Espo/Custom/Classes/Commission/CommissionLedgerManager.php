<?php

namespace Espo\Custom\Classes\Commission;

use DateInterval;
use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class CommissionLedgerManager
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function upsertFromPolicy(Entity $policy): void
    {
        $policyId = (string) ($policy->getId() ?? '');
        if ($policyId === '') {
            return;
        }

        $commissionType = $this->mapPolicyCommissionType($policy);
        $effectiveDate = (string) ($policy->get('effective_date') ?? '');
        $key = $this->buildLedgerKey($policyId, $commissionType, $effectiveDate, 'policy', $policyId);
        $rate = $this->normalizeRateOrNull($policy->get('commission_rate'));
        $premium = (float) ($policy->get('premium_amount') ?? 0);

        $this->upsertByKey($key, [
            'name' => $this->buildCommissionName($policy->get('accountName'), $commissionType),
            'commissionType' => $commissionType,
            'status' => 'Estimated',
            'policyId' => $policyId,
            'policyName' => $policy->get('name'),
            'accountId' => $policy->get('accountId'),
            'accountName' => $policy->get('accountName'),
            'contactId' => $policy->get('contactId'),
            'contactName' => $policy->get('contactName'),
            'assignedUserId' => $policy->get('assignedUserId'),
            'assignedUserName' => $policy->get('assignedUserName'),
            'producer' => $policy->get('assignedUserName'),
            'carrier' => $policy->get('carrier'),
            'lineOfBusiness' => $this->normalizeLineOfBusiness($policy->get('line_of_business')),
            'writtenPremium' => $premium,
            'commissionRate' => $rate,
            'estimatedCommission' => $this->calculateEstimatedCommission($premium, $rate),
            'effectiveDate' => $effectiveDate !== '' ? $effectiveDate : null,
            'expectedPaymentDate' => $this->calculateExpectedPaymentDate($effectiveDate),
        ]);
    }

    public function upsertFromRenewal(Entity $renewal): void
    {
        $renewalId = (string) ($renewal->getId() ?? '');
        $policyId = (string) ($renewal->get('newPolicyId') ?? $renewal->get('policyId') ?? '');
        if ($renewalId === '' || $policyId === '') {
            return;
        }

        $effectiveDate = (string) ($renewal->get('renewal_effective_date') ?? $renewal->get('expiration_date') ?? '');
        $key = $this->buildLedgerKey($policyId, 'Renewal', $effectiveDate, 'renewal', $renewalId);
        $rate = $this->normalizeRateOrNull($renewal->get('commission_rate'));
        $premium = (float) ($renewal->get('renewal_premium') ?? $renewal->get('current_premium') ?? 0);

        $policy = $this->entityManager->getEntityById('Policy', $policyId);

        $this->upsertByKey($key, [
            'name' => $this->buildCommissionName($renewal->get('accountName'), 'Renewal'),
            'commissionType' => 'Renewal',
            'status' => 'Estimated',
            'renewalId' => $renewalId,
            'renewalName' => $renewal->get('name'),
            'policyId' => $policyId,
            'policyName' => $policy?->get('name') ?? $renewal->get('policyName'),
            'accountId' => $renewal->get('accountId'),
            'accountName' => $renewal->get('accountName'),
            'contactId' => $renewal->get('contactId'),
            'contactName' => $renewal->get('contactName'),
            'assignedUserId' => $renewal->get('assignedUserId'),
            'assignedUserName' => $renewal->get('assignedUserName'),
            'producer' => $renewal->get('assignedUserName'),
            'carrier' => $renewal->get('carrier') ?: ($policy?->get('carrier')),
            'lineOfBusiness' => $this->normalizeLineOfBusiness($renewal->get('line_of_business')),
            'writtenPremium' => $premium,
            'commissionRate' => $rate,
            'estimatedCommission' => $this->calculateEstimatedCommission($premium, $rate),
            'effectiveDate' => $effectiveDate !== '' ? $effectiveDate : null,
            'expectedPaymentDate' => $this->calculateExpectedPaymentDate($effectiveDate),
        ]);
    }

    public function upsertFromOpportunity(Entity $opportunity): void
    {
        $opportunityId = (string) ($opportunity->getId() ?? '');
        if ($opportunityId === '') {
            return;
        }

        $policyId = $this->resolvePolicyIdForOpportunity($opportunity);
        if ($policyId === '') {
            return;
        }

        $policy = $this->entityManager->getEntityById('Policy', $policyId);
        if (!$policy) {
            return;
        }

        $commissionType = $this->mapOpportunityCommissionType((string) ($opportunity->get('business_type') ?? ''));
        $effectiveDate = (string) ($opportunity->get('effective_date') ?? $policy->get('effective_date') ?? '');
        $key = $this->buildLedgerKey($policyId, $commissionType, $effectiveDate, 'opportunity', $opportunityId);
        $rate = $this->normalizeRateOrNull($opportunity->get('commission_rate') ?? $policy->get('commission_rate'));
        $premium = (float) ($opportunity->get('written_premium') ?? $policy->get('premium_amount') ?? 0);

        $this->upsertByKey($key, [
            'name' => $this->buildCommissionName($opportunity->get('accountName'), $commissionType),
            'commissionType' => $commissionType,
            'status' => 'Estimated',
            'opportunityId' => $opportunityId,
            'opportunityName' => $opportunity->get('name'),
            'policyId' => $policyId,
            'policyName' => $policy->get('name'),
            'accountId' => $opportunity->get('accountId') ?: $policy->get('accountId'),
            'accountName' => $opportunity->get('accountName') ?: $policy->get('accountName'),
            'contactId' => $opportunity->get('contactId') ?: $policy->get('contactId'),
            'contactName' => $opportunity->get('contactName') ?: $policy->get('contactName'),
            'assignedUserId' => $opportunity->get('assignedUserId') ?: $policy->get('assignedUserId'),
            'assignedUserName' => $opportunity->get('assignedUserName') ?: $policy->get('assignedUserName'),
            'producer' => $opportunity->get('assignedUserName') ?: $policy->get('assignedUserName'),
            'carrier' => $opportunity->get('carrier') ?: $policy->get('carrier'),
            'lineOfBusiness' => $this->normalizeLineOfBusiness($opportunity->get('line_of_business') ?: $policy->get('line_of_business')),
            'writtenPremium' => $premium,
            'commissionRate' => $rate,
            'estimatedCommission' => $this->calculateEstimatedCommission($premium, $rate),
            'effectiveDate' => $effectiveDate !== '' ? $effectiveDate : null,
            'expectedPaymentDate' => $this->calculateExpectedPaymentDate($effectiveDate),
        ]);
    }

    private function upsertByKey(string $ledgerKey, array $payload): void
    {
        $commission = $this->entityManager
            ->getRDBRepository('Commission')
            ->where(['ledgerKey' => $ledgerKey])
            ->findOne();

        if (!$commission) {
            $commission = $this->entityManager->getNewEntity('Commission');
            $commission->set('ledgerKey', $ledgerKey);
        }

        $commission->set($payload);
        $this->entityManager->saveEntity($commission);
    }

    private function mapPolicyCommissionType(Entity $policy): string
    {
        $businessType = strtolower(trim((string) ($policy->get('business_type') ?? '')));

        if (str_contains($businessType, 'renew')) {
            return 'Renewal';
        }

        if (str_contains($businessType, 'rewrite') || str_contains($businessType, 'endorse')) {
            return 'Endorsement';
        }

        return 'New Business';
    }

    private function mapOpportunityCommissionType(string $businessType): string
    {
        return match (trim($businessType)) {
            'Renewal' => 'Renewal',
            'Rewrite' => 'Endorsement',
            default => 'New Business',
        };
    }

    private function resolvePolicyIdForOpportunity(Entity $opportunity): string
    {
        $policyId = trim((string) ($opportunity->get('policy_stub_id') ?? ''));
        if ($policyId !== '') {
            return $policyId;
        }

        $policyNumber = trim((string) ($opportunity->get('policy_number') ?? ''));
        $accountId = trim((string) ($opportunity->get('accountId') ?? ''));
        if ($policyNumber === '' || $accountId === '') {
            return '';
        }

        $policy = $this->entityManager
            ->getRDBRepository('Policy')
            ->where([
                'policy_number' => $policyNumber,
                'accountId' => $accountId,
            ])
            ->findOne();

        return $policy ? (string) $policy->getId() : '';
    }

    private function buildLedgerKey(
        string $policyId,
        string $commissionType,
        string $effectiveDate,
        string $sourceType,
        string $sourceId
    ): string {
        $window = $effectiveDate !== '' ? substr($effectiveDate, 0, 7) : 'no-effective-date';

        return implode('|', [$policyId, $commissionType, $window, $sourceType . ':' . $sourceId]);
    }

    private function buildCommissionName(mixed $accountName, string $commissionType): string
    {
        $account = trim((string) $accountName);
        if ($account === '') {
            $account = 'Client';
        }

        return sprintf('%s - %s Commission', $account, $commissionType);
    }

    private function calculateExpectedPaymentDate(string $effectiveDate): ?string
    {
        if ($effectiveDate === '') {
            return null;
        }

        return (new DateTimeImmutable($effectiveDate))
            ->add(new DateInterval('P30D'))
            ->format('Y-m-d');
    }

    private function calculateEstimatedCommission(float $premium, ?float $rate): float
    {
        if ($rate === null) {
            return 0.0;
        }

        return round($premium * $rate, 2);
    }

    private function normalizeRateOrNull(mixed $rate): ?float
    {
        if ($rate === null || $rate === '') {
            return null;
        }

        $numericRate = (float) $rate;

        return $numericRate > 1 ? $numericRate / 100 : $numericRate;
    }

    private function normalizeLineOfBusiness(mixed $value): string
    {
        $line = trim((string) $value);

        return match ($line) {
            'GL' => 'General Liability',
            'Auto' => 'Personal Auto',
            'Home' => 'Homeowners',
            default => $line === '' ? 'Other' : $line,
        };
    }
}
