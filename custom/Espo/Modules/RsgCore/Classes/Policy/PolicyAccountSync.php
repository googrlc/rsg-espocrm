<?php

namespace Espo\Modules\RsgCore\Classes\Policy;

use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Account\AccountNameResolution;
use Espo\Custom\Classes\Policy\PolicyHealthManager;
use Espo\Custom\Classes\Policy\PolicyStatusSets;
use Espo\Custom\Classes\Renewal\RenewalLeadWindows;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class PolicyAccountSync
{

    public function __construct(
        private EntityManager $entityManager,
        private PolicyHealthManager $policyHealthManager
    ) {}

    public function applyDerivedFields(Entity $policy): void
    {
        $resolved = AccountNameResolution::resolveForPolicy($this->entityManager, $policy);
        $current = trim((string) ($policy->get('accountName') ?? ''));
        if ($resolved !== '' && ($current === '' || AccountNameResolution::isPlaceholder($current))) {
            $policy->set('accountName', $resolved);
        }

        $this->applyLineOfBusinessFromSync($policy);

        $accountName = $policy->get('accountName') ?: '';
        $lineOfBusiness = $policy->get('line_of_business') ?: $policy->get('business_type') ?: '';
        $policyNumber = $policy->get('policy_number') ?: '';

        $nameParts = array_values(array_filter([$accountName, $lineOfBusiness, $policyNumber]));
        if ($nameParts !== []) {
            $policy->set('name', implode(' | ', $nameParts));
        }

        if ($policy->get('carrierAccountName')) {
            $policy->set('carrier', $policy->get('carrierAccountName'));
        }

        $daysRemaining = $this->calculateDaysRemaining($policy->get('expiration_date'));
        $policy->set('daysRemaining', $daysRemaining);

        $statusLabel = 'ACTIVE';
        $urgencyIcon = 'OK';
        if ($daysRemaining === null) {
            $statusLabel = 'UNKNOWN';
            $urgencyIcon = '?';
        } elseif ($daysRemaining <= 0) {
            $statusLabel = 'EXPIRED';
            $urgencyIcon = '!!';
        } elseif ($daysRemaining <= 30) {
            $statusLabel = 'CRITICAL RENEWAL';
            $urgencyIcon = '!';
        } elseif ($daysRemaining <= 60) {
            $statusLabel = 'RENEWAL WINDOW';
        }
        $policy->set('statusLabel', $statusLabel);
        $policy->set('urgencyIcon', $urgencyIcon);

        if ($daysRemaining === null || $daysRemaining <= 0) {
            $policy->set('urgency', null);
        } elseif ($daysRemaining <= 30) {
            $policy->set('urgency', 'Critical');
        } elseif ($daysRemaining <= 60) {
            $policy->set('urgency', 'High');
        } elseif ($daysRemaining <= 90) {
            $policy->set('urgency', 'Medium');
        } else {
            $policy->set('urgency', 'Low');
        }

        $premiumAmount = (float) ($policy->get('premium_amount') ?? 0);
        $normalizedRate = $this->normalizeRate($policy->get('commission_rate'));
        // No stored rate -> do not fabricate an amount from an assumed default.
        $policy->set(
            'commissionAmount',
            $normalizedRate === null ? null : round($premiumAmount * $normalizedRate, 2)
        );

        $status = (string) ($policy->get('status') ?? '');
        $renewalLeadDays = RenewalLeadWindows::leadDaysForPolicy($policy);
        if ($daysRemaining !== null && $daysRemaining <= 0) {
            // Expiration date has passed: resolve to a single terminal state instead of
            // letting a negative day count flow into renewal-urgency logic. 'Renewed' is a
            // completed renewal, not a lapse, so it is excluded from the auto-expire set.
            if (in_array($status, PolicyStatusSets::LAPSE_ON_EXPIRY, true)) {
                $status = 'Expired';
                $policy->set('status', $status);
            }
        } elseif ($status === 'Active' && $daysRemaining !== null && $daysRemaining <= $renewalLeadDays) {
            $status = 'Up for Renewal';
            $policy->set('status', $status);
        }

        $isExpired = $daysRemaining !== null && $daysRemaining <= 0;
        $policy->set(
            'premiumAtRisk',
            (in_array($status, PolicyStatusSets::ACTIVE, true) && !$isExpired) ? $premiumAmount : 0.0
        );

        if ($policyNumber !== '') {
            $policy->set('carrierPortalUrl', 'https://carrier-portal.com/policy/' . rawurlencode((string) $policyNumber));
        }

        $this->policyHealthManager->applyToPolicy($policy);
    }

    /**
     * Persists upstream LOB snapshot and a normalized varchar for reporting / Renewal automation.
     * Raw input precedence: explicit line_of_business_raw, then unchanged line_of_business, then business_type.
     */
    private function applyLineOfBusinessFromSync(Entity $policy): void
    {
        $rawCandidate = trim((string) ($policy->get('line_of_business_raw') ?? ''));
        if ($rawCandidate === '') {
            $rawCandidate = trim((string) ($policy->get('line_of_business') ?? ''));
        }
        if ($rawCandidate === '') {
            $rawCandidate = trim((string) ($policy->get('business_type') ?? ''));
        }

        if ($rawCandidate !== '') {
            $policy->set('line_of_business_raw', $rawCandidate);
        }

        $normalized = $this->normalizeLineOfBusinessValue($rawCandidate !== '' ? $rawCandidate : null);

        if ($normalized !== '') {
            $policy->set('line_of_business', $normalized);
        }
    }

    private function normalizeLineOfBusinessValue(?string $value): string
    {
        $line = trim((string) $value);
        if ($line === '') {
            return '';
        }

        return match ($line) {
            'GL' => 'General Liability',
            'Auto', 'PersonalAuto' => 'Personal Auto',
            'Home' => 'Homeowners',
            'renters' => 'Renters',
            'work comp' => 'Workers Compensation',
            'Life insurance' => 'Life',
            'Builders risk / Home Construction' => 'Builders Risk',
            'Cyber/Network Liability' => 'Cyber Liability',
            // 'personal lines' is a category, not a specific LOB — left unmapped on purpose.
            default => $line,
        };
    }

    public function refreshAccountMetricsByPolicy(Entity $policy): void
    {
        $accountIds = array_filter(array_unique([
            $policy->get('accountId'),
            $policy->getFetched('accountId'),
        ]));

        foreach ($accountIds as $accountId) {
            $this->refreshAccountMetricsById((string) $accountId);
        }

        $carrierAccountIds = array_filter(array_unique([
            $policy->get('carrierAccountId'),
            $policy->getFetched('carrierAccountId'),
        ]));

        foreach ($carrierAccountIds as $carrierAccountId) {
            $this->refreshCarrierPremiumById((string) $carrierAccountId);
        }
    }

    public function refreshAccountMetricsById(string $accountId): void
    {
        $account = $this->entityManager->getEntityById('Account', $accountId);
        if (!$account) {
            return;
        }

        $policyList = $this->entityManager
            ->getRDBRepository('Policy')
            ->where(['accountId' => $accountId])
            ->find();

        $today = new DateTimeImmutable('today');
        $totalPremium = 0.0;
        $activePolicyCount = 0;
        $nextExpiration = null;
        $nextExpirationLob = null;
        $nextExpirationCarrier = null;

        foreach ($policyList as $policy) {
            $status = (string) ($policy->get('status') ?? '');
            $expirationDateRaw = $policy->get('expiration_date');
            $expirationDate = $this->tryParseDate($expirationDateRaw);

            if (in_array($status, PolicyStatusSets::ACTIVE, true)) {
                $totalPremium += (float) ($policy->get('premium_amount') ?? 0);
                $activePolicyCount++;

                if ($expirationDate && (!$nextExpiration || $expirationDate < $nextExpiration)) {
                    $nextExpiration = $expirationDate;
                    $nextExpirationLob = $policy->get('line_of_business');
                    $nextExpirationCarrier = $policy->get('carrier');
                }
            }
        }

        $account->set('total_active_premium', round($totalPremium, 2));
        $account->set('activePolicyCount', $activePolicyCount);
        $account->set('policyCountActive', $activePolicyCount);
        $account->set('next_x_date', $nextExpiration?->format('Y-m-d'));
        $account->set('next_x_date_lob', $nextExpirationLob);
        $account->set('next_renewal_date', $nextExpiration?->format('Y-m-d'));
        $account->set('next_renewal_lob', $nextExpirationLob);
        $account->set('next_renewal_carrier', $nextExpirationCarrier);
        $account->set('days_to_renewal', $nextExpiration ? (int) $today->diff($nextExpiration)->format('%r%a') : null);

        $this->entityManager->saveEntity($account, [SaveOption::SILENT => true, SaveOption::SKIP_HOOKS => true]);
    }

    public function refreshCarrierPremiumById(string $carrierAccountId): void
    {
        $account = $this->entityManager->getEntityById('Account', $carrierAccountId);
        if (!$account) {
            return;
        }

        $policyList = $this->entityManager
            ->getRDBRepository('Policy')
            ->where([
                'carrierAccountId' => $carrierAccountId,
                'status' => PolicyStatusSets::ACTIVE,
            ])
            ->find();

        $totalCarrierPremium = 0.0;

        foreach ($policyList as $policy) {
            $totalCarrierPremium += (float) ($policy->get('premium_amount') ?? 0);
        }

        $account->set('total_carrier_premium', round($totalCarrierPremium, 2));

        $this->entityManager->saveEntity($account, [SaveOption::SILENT => true, SaveOption::SKIP_HOOKS => true]);
    }

    private function calculateDaysRemaining(?string $expirationDate): ?int
    {
        if (!$expirationDate) {
            return null;
        }

        $date = $this->tryParseDate($expirationDate);
        if (!$date) {
            return null;
        }

        $today = new DateTimeImmutable('today');

        return (int) $today->diff($date)->format('%r%a');
    }

    private function tryParseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeRate(mixed $rate): ?float
    {
        if ($rate === null || $rate === '') {
            return null;
        }

        $numericRate = (float) $rate;

        return $numericRate > 1 ? $numericRate / 100 : $numericRate;
    }
}
