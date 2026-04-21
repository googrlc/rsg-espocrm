<?php

namespace Espo\Custom\Classes\Policy;

use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Account\AccountNameResolution;
use Espo\Custom\Classes\Renewal\RenewalLeadWindows;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class PolicyAccountSync
{
    private const ACTIVE_STATUSES = [
        'Active',
        'Up for Renewal',
        'Renewing',
        'Renewed',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function applyDerivedFields(Entity $policy): void
    {
        $resolved = AccountNameResolution::resolveForPolicy($this->entityManager, $policy);
        $current = trim((string) ($policy->get('accountName') ?? ''));
        if ($resolved !== '' && ($current === '' || AccountNameResolution::isPlaceholder($current))) {
            $policy->set('accountName', $resolved);
        }

        $accountName = $policy->get('accountName') ?: '';
        $lineOfBusiness = $policy->get('lineOfBusiness') ?: $policy->get('businessType') ?: '';
        $policyNumber = $policy->get('policyNumber') ?: '';

        $nameParts = array_values(array_filter([$accountName, $lineOfBusiness, $policyNumber]));
        if ($nameParts !== []) {
            $policy->set('name', implode(' | ', $nameParts));
        }

        if ($policy->get('carrierAccountName')) {
            $policy->set('carrier', $policy->get('carrierAccountName'));
        }

        $daysRemaining = $this->calculateDaysRemaining($policy->get('expirationDate'));
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

        if ($daysRemaining === null) {
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

        $premiumAmount = (float) ($policy->get('premiumAmount') ?? 0);
        $normalizedRate = $this->normalizeRate($policy->get('commissionRate'));
        $policy->set('commissionAmount', round($premiumAmount * $normalizedRate, 2));

        $status = (string) ($policy->get('status') ?? '');
        $renewalLeadDays = RenewalLeadWindows::leadDaysForPolicy($policy);
        if ($status === 'Active' && $daysRemaining !== null && $daysRemaining <= $renewalLeadDays) {
            $status = 'Up for Renewal';
            $policy->set('status', $status);
        }

        $policy->set(
            'premiumAtRisk',
            in_array($status, self::ACTIVE_STATUSES, true) ? $premiumAmount : 0.0
        );

        if ($policyNumber !== '') {
            $policy->set('carrierPortalUrl', 'https://carrier-portal.com/policy/' . rawurlencode($policyNumber));
        }
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
            $expirationDateRaw = $policy->get('expirationDate');
            $expirationDate = $expirationDateRaw ? new DateTimeImmutable($expirationDateRaw) : null;

            if (in_array($status, self::ACTIVE_STATUSES, true)) {
                $totalPremium += (float) ($policy->get('premiumAmount') ?? 0);
                $activePolicyCount++;

                if ($expirationDate && (!$nextExpiration || $expirationDate < $nextExpiration)) {
                    $nextExpiration = $expirationDate;
                    $nextExpirationLob = $policy->get('lineOfBusiness');
                    $nextExpirationCarrier = $policy->get('carrier');
                }
            }
        }

        $account->set('totalActivePremium', round($totalPremium, 2));
        $account->set('activePolicyCount', $activePolicyCount);
        $account->set('policyCountActive', $activePolicyCount);
        $account->set('nextXDate', $nextExpiration?->format('Y-m-d'));
        $account->set('nextXDateLob', $nextExpirationLob);
        $account->set('nextRenewalDate', $nextExpiration?->format('Y-m-d'));
        $account->set('nextRenewalLob', $nextExpirationLob);
        $account->set('nextRenewalCarrier', $nextExpirationCarrier);
        $account->set('daysToRenewal', $nextExpiration ? (int) $today->diff($nextExpiration)->format('%r%a') : null);

        $this->entityManager->saveEntity($account, [SaveOption::SILENT => true]);
    }

    private function calculateDaysRemaining(?string $expirationDate): ?int
    {
        if (!$expirationDate) {
            return null;
        }

        $today = new DateTimeImmutable('today');
        $date = new DateTimeImmutable($expirationDate);

        return (int) $today->diff($date)->format('%r%a');
    }

    private function normalizeRate(mixed $rate): float
    {
        if ($rate === null || $rate === '') {
            return 0.10;
        }

        $numericRate = (float) $rate;

        return $numericRate > 1 ? $numericRate / 100 : $numericRate;
    }
}
