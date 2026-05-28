<?php

namespace Espo\Custom\Classes\Policy;

use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class PolicyHealthManager
{
    private const EXPIRED_STATUSES = [
        'Expired',
        'Cancelled',
        'Flat Cancel',
        'Pending Cancel',
        'Non-Renewed',
        'Lapsed',
    ];

    private const RENEWING_STATUSES = [
        'Up for Renewal',
        'Renewing',
    ];

    private const FINAL_RENEWAL_STAGES = [
        'Renewed - Won',
        'Lost',
    ];

    private const URGENT_DAYS_THRESHOLD = 15;
    private const RATE_SPIKE_URGENT_PCT = 25.0;
    private const RATE_SPIKE_AT_RISK_PCT = 15.0;
    private const SIGNAL_LOOKBACK_DAYS = 45;
    private const BILLING_LOOKBACK_DAYS = 90;

    public function __construct(
        private EntityManager $entityManager
    ) {}

    /**
     * Compute and set policy_health on the entity. Caller is responsible for saving.
     * Expects daysRemaining and status to already be set (PolicyAccountSync runs first).
     */
    public function applyToPolicy(Entity $policy): void
    {
        $policy->set('policy_health', $this->computeHealth($policy));
    }

    private function computeHealth(Entity $policy): string
    {
        $status = trim((string) ($policy->get('status') ?? ''));
        $daysRemaining = $policy->get('daysRemaining');
        $daysRemaining = is_numeric($daysRemaining) ? (int) $daysRemaining : null;

        if (in_array($status, self::EXPIRED_STATUSES, true)) {
            return 'Expired';
        }

        $policyId = (string) ($policy->getId() ?? '');
        $signals = $this->analyzePolicySignals($policyId);

        if (
            $signals['hasRecentCancellationSignal'] ||
            ($daysRemaining !== null && $daysRemaining <= self::URGENT_DAYS_THRESHOLD) ||
            $signals['maxPremiumSpikePct'] >= self::RATE_SPIKE_URGENT_PCT
        ) {
            return 'Urgent';
        }

        if (in_array($status, self::RENEWING_STATUSES, true) || $this->hasOpenRenewal($policyId)) {
            return 'Renewing';
        }

        if (
            $signals['hasRecentCoverageReduction'] ||
            $signals['hasRecentBillingIssue'] ||
            $signals['maxPremiumSpikePct'] >= self::RATE_SPIKE_AT_RISK_PCT
        ) {
            return 'At Risk';
        }

        return 'Active';
    }

    private function analyzePolicySignals(string $policyId): array
    {
        $signals = [
            'hasRecentCancellationSignal' => false,
            'hasRecentCoverageReduction' => false,
            'hasRecentBillingIssue' => false,
            'maxPremiumSpikePct' => 0.0,
        ];

        if ($policyId === '') {
            return $signals;
        }

        $activityList = $this->entityManager
            ->getRDBRepository('ActivityLog')
            ->where(['policyId' => $policyId])
            ->find();

        $today = new DateTimeImmutable('today');

        foreach ($activityList as $activity) {
            $loggedAt = $this->toDateTime((string) ($activity->get('dateTime') ?? ''));
            if (!$loggedAt) {
                continue;
            }

            $daysAgo = (int) $loggedAt->diff($today)->format('%r%a');
            $activityType = trim((string) ($activity->get('activityType') ?? ''));
            $classification = trim((string) ($activity->get('classification') ?? ''));
            $changeType = trim((string) ($activity->get('changeType') ?? ''));

            if ($daysAgo <= self::SIGNAL_LOOKBACK_DAYS) {
                if (
                    $activityType === 'Cancellation' ||
                    $classification === 'Cancellation / non-renewal notice' ||
                    $changeType === 'Cancellation'
                ) {
                    $signals['hasRecentCancellationSignal'] = true;
                }

                if ($activityType === 'Coverage Remove') {
                    $signals['hasRecentCoverageReduction'] = true;
                }

                if ($activityType === 'Premium Change') {
                    $signals['maxPremiumSpikePct'] = max(
                        $signals['maxPremiumSpikePct'],
                        $this->calculatePremiumChangePct($activity)
                    );
                }
            }

            if (
                $daysAgo <= self::BILLING_LOOKBACK_DAYS &&
                in_array($classification, ['Payment / billing', 'Complaint'], true)
            ) {
                $signals['hasRecentBillingIssue'] = true;
            }
        }

        $signals['maxPremiumSpikePct'] = round($signals['maxPremiumSpikePct'], 2);

        return $signals;
    }

    private function hasOpenRenewal(string $policyId): bool
    {
        if ($policyId === '') {
            return false;
        }

        $renewalList = $this->entityManager
            ->getRDBRepository('Renewal')
            ->where(['policyId' => $policyId])
            ->find();

        foreach ($renewalList as $renewal) {
            $stage = trim((string) ($renewal->get('stage') ?? ''));
            if ($stage !== '' && !in_array($stage, self::FINAL_RENEWAL_STAGES, true)) {
                return true;
            }
        }

        return false;
    }

    private function calculatePremiumChangePct(Entity $activity): float
    {
        $oldPremium = (float) ($activity->get('oldPremium') ?? 0);
        $newPremium = (float) ($activity->get('newPremium') ?? 0);
        $premiumDelta = $activity->get('premiumDelta');
        $delta = $premiumDelta !== null && $premiumDelta !== ''
            ? (float) $premiumDelta
            : $newPremium - $oldPremium;

        if ($delta <= 0) {
            return 0.0;
        }

        if ($oldPremium > 0) {
            return round(($delta / $oldPremium) * 100, 2);
        }

        return $delta >= 500 ? 15.0 : 0.0;
    }

    private function toDateTime(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(substr(str_replace('T', ' ', $value), 0, 19));
        } catch (\Throwable) {
            return null;
        }
    }
}
