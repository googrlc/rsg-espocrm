<?php

namespace Espo\Custom\Classes\Account;

use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AccountHealthManager
{
    private const ACTIVE_POLICY_STATUSES = [
        'Active',
        'Up for Renewal',
        'Renewing',
        'Renewed',
    ];

    private const RENEWAL_POLICY_STATUSES = [
        'Up for Renewal',
        'Renewing',
    ];

    private const CANCELLATION_POLICY_STATUSES = [
        'Pending Cancel',
        'Cancelled',
        'Flat Cancel',
        'Non-Renewed',
        'Lapsed',
    ];

    private const FINAL_RENEWAL_STAGES = [
        'Renewed - Won',
        'Lost',
    ];

    private const GAP_FIELDS = [
        'gapUmbrella',
        'gapLife',
        'gapRenters',
        'gapAutoUm',
        'gapMedicare',
        'gapLandlord',
        'gapRideshare',
        'gapFinalExpense',
    ];

    private const CLIENT_TOUCH_TYPES = [
        'Email Out',
        'Email In',
        'Call',
        'Renewal Outreach',
        'Endorsement',
        'Premium Change',
        'Coverage Add',
        'Coverage Remove',
        'Cancellation',
        'Note',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function refreshByAccountId(string $accountId): void
    {
        if ($accountId === '') {
            return;
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);
        if (!$account) {
            return;
        }

        $this->applyToAccount($account);
        $this->entityManager->saveEntity($account, [SaveOption::SILENT => true]);
    }

    public function applyToAccount(Entity $account): void
    {
        $accountId = (string) ($account->getId() ?? '');
        $policyList = $accountId !== ''
            ? $this->entityManager->getRDBRepository('Policy')->where(['accountId' => $accountId])->find()
            : [];
        $renewalList = $accountId !== ''
            ? $this->entityManager->getRDBRepository('Renewal')->where(['accountId' => $accountId])->find()
            : [];
        $taskList = $accountId !== ''
            ? $this->entityManager
                ->getRDBRepository('Task')
                ->where([
                    'linkedAccountId' => $accountId,
                    'status!=' => ['Completed', 'Cancelled'],
                ])
                ->find()
            : [];
        $activityList = $accountId !== ''
            ? $this->entityManager->getRDBRepository('ActivityLog')->where(['accountId' => $accountId])->find()
            : [];

        $today = new DateTimeImmutable('today');
        $policySignals = $this->analyzePolicies($account, $policyList, $renewalList, $today);
        $activitySignals = $this->analyzeActivities($activityList, $today);
        $taskSignals = $this->analyzeTasks($taskList, $today);

        $gapCount = $this->countGapFlags($account);
        $rateIncreasePct = max(
            round((float) ($account->get('premiumChangePct') ?? 0), 2),
            $activitySignals['maxPremiumSpikePct']
        );
        $rateIncreaseFlag = $rateIncreasePct >= 15.0;

        $scoreBundleDepth = $this->scoreBundleDepth($policySignals['coverageDepth'], $gapCount);
        $scorePaymentHistory = $this->scorePaymentHistory(
            $activitySignals['hasRecentCancellationSignal'],
            $activitySignals['hasRecentBillingIssue'],
            $taskSignals['overdueUrgentCount'],
            $taskSignals['openRescueCount'],
            $rateIncreasePct
        );
        $scoreYearsRetained = $this->scoreYearsRetained($policySignals['oldestEffectiveDate'], $today);
        $scoreClaimsActivity = $this->scoreClaimsActivity($activitySignals['claimsCount3y']);
        $scoreLastContact = $this->scoreLastContact($activitySignals['daysSinceMeaningfulActivity']);

        $scoreTotal = $scoreBundleDepth
            + $scorePaymentHistory
            + $scoreYearsRetained
            + $scoreClaimsActivity
            + $scoreLastContact;
        $scoreTier = $this->resolveScoreTier($scoreTotal);

        $previousScore = (int) ($account->getFetched('scoreTotal') ?? $account->get('scoreTotal') ?? 0);
        $scoreChangeAmount = $scoreTotal - $previousScore;

        $account->set('gapCount', $gapCount);
        $account->set('scoreBundleDepth', $scoreBundleDepth);
        $account->set('scorePaymentHistory', $scorePaymentHistory);
        $account->set('scoreYearsRetained', $scoreYearsRetained);
        $account->set('scoreClaimsActivity', $scoreClaimsActivity);
        $account->set('scoreLastContact', $scoreLastContact);
        $account->set('scoreTotal', $scoreTotal);
        $account->set('scoreTier', $scoreTier);
        $account->set('scoreLastCalculated', gmdate('Y-m-d H:i:s'));
        $account->set('scoreChangeDirection', $this->resolveChangeDirection($scoreChangeAmount));
        $account->set('scoreChangeAmount', abs($scoreChangeAmount));
        $account->set('rateIncreaseFlag', $rateIncreaseFlag);
        $account->set('nextRenewalDate', $policySignals['nextRenewalDate']);
        $account->set('nextRenewalLob', $policySignals['nextRenewalLob']);
        $account->set('nextRenewalCarrier', $policySignals['nextRenewalCarrier']);
        $account->set('daysToRenewal', $policySignals['daysToRenewal']);
        $account->set('accountStatus', $this->determineAccountStatus(
            $policySignals,
            $activitySignals,
            $taskSignals,
            $scoreTotal,
            $rateIncreasePct
        ));
    }

    private function analyzePolicies(Entity $account, iterable $policyList, iterable $renewalList, DateTimeImmutable $today): array
    {
        $coverageSet = [];
        $carrierSet = [];
        $activePolicyCount = 0;
        $oldestEffectiveDate = null;
        $nextRenewalDate = null;
        $nextRenewalLob = null;
        $nextRenewalCarrier = null;
        $daysToRenewal = null;
        $hasRenewingMotion = false;
        $hasUrgentRenewal = false;
        $hasCancellationStatus = false;

        foreach ($policyList as $policy) {
            $status = trim((string) ($policy->get('status') ?? ''));
            $line = $this->normalizeLine((string) ($policy->get('lineOfBusiness') ?? $policy->get('businessType') ?? ''));
            $carrier = trim((string) ($policy->get('carrier') ?? ''));

            if (in_array($status, self::ACTIVE_POLICY_STATUSES, true)) {
                $activePolicyCount++;

                if ($line !== '') {
                    $coverageSet[$line] = true;
                }

                if ($carrier !== '') {
                    $carrierSet[$carrier] = true;
                }

                $effectiveDate = $this->toDate((string) ($policy->get('effectiveDate') ?? ''));
                if ($effectiveDate && ($oldestEffectiveDate === null || $effectiveDate < $oldestEffectiveDate)) {
                    $oldestEffectiveDate = $effectiveDate;
                }

                $expirationDate = $this->toDate((string) ($policy->get('expirationDate') ?? ''));
                if ($expirationDate && ($nextRenewalDate === null || $expirationDate < $nextRenewalDate)) {
                    $nextRenewalDate = $expirationDate;
                    $nextRenewalLob = $line !== '' ? $line : null;
                    $nextRenewalCarrier = $carrier !== '' ? $carrier : null;
                }
            }

            if (in_array($status, self::RENEWAL_POLICY_STATUSES, true)) {
                $hasRenewingMotion = true;
            }

            if (in_array($status, self::CANCELLATION_POLICY_STATUSES, true)) {
                $hasCancellationStatus = true;
            }
        }

        foreach ($this->normalizeMultiEnum($account->get('lob')) as $line) {
            $coverageSet[$line] = true;
        }

        if ($nextRenewalDate) {
            $daysToRenewal = (int) $today->diff($nextRenewalDate)->format('%r%a');
            $hasUrgentRenewal = $daysToRenewal <= 15;
        }

        foreach ($renewalList as $renewal) {
            $stage = trim((string) ($renewal->get('stage') ?? ''));
            if ($stage !== '' && !in_array($stage, self::FINAL_RENEWAL_STAGES, true)) {
                $hasRenewingMotion = true;
            }

            $urgency = trim((string) ($renewal->get('urgency') ?? ''));
            if ($urgency === 'Critical') {
                $hasUrgentRenewal = true;
            }
        }

        if ($carrierSet === []) {
            foreach ($this->extractCarrierValues((string) ($account->get('carrier') ?? '')) as $carrier) {
                $carrierSet[$carrier] = true;
            }
        }

        return [
            'activePolicyCount' => $activePolicyCount,
            'coverageDepth' => count($coverageSet),
            'carrierMixCount' => count($carrierSet),
            'oldestEffectiveDate' => $oldestEffectiveDate,
            'nextRenewalDate' => $nextRenewalDate?->format('Y-m-d'),
            'nextRenewalLob' => $nextRenewalLob,
            'nextRenewalCarrier' => $nextRenewalCarrier,
            'daysToRenewal' => $daysToRenewal,
            'hasRenewingMotion' => $hasRenewingMotion,
            'hasUrgentRenewal' => $hasUrgentRenewal,
            'hasCancellationStatus' => $hasCancellationStatus,
        ];
    }

    private function analyzeActivities(iterable $activityList, DateTimeImmutable $today): array
    {
        $lastMeaningfulActivity = null;
        $hasRecentCancellationSignal = false;
        $hasRecentCoverageReduction = false;
        $hasRecentBillingIssue = false;
        $claimsCount3y = 0;
        $maxPremiumSpikePct = 0.0;

        foreach ($activityList as $activity) {
            $loggedAt = $this->toDateTime((string) ($activity->get('dateTime') ?? ''));
            if (!$loggedAt) {
                continue;
            }

            $activityType = trim((string) ($activity->get('activityType') ?? ''));
            $classification = trim((string) ($activity->get('classification') ?? ''));

            if (
                in_array($activityType, self::CLIENT_TOUCH_TYPES, true) &&
                ($lastMeaningfulActivity === null || $loggedAt > $lastMeaningfulActivity)
            ) {
                $lastMeaningfulActivity = $loggedAt;
            }

            $daysAgo = (int) $loggedAt->diff($today)->format('%r%a');

            if ($daysAgo <= 1095 && $classification === 'Claim related') {
                $claimsCount3y++;
            }

            if ($daysAgo <= 45) {
                if (
                    $activityType === 'Cancellation' ||
                    $classification === 'Cancellation / non-renewal notice' ||
                    trim((string) ($activity->get('changeType') ?? '')) === 'Cancellation'
                ) {
                    $hasRecentCancellationSignal = true;
                }

                if ($activityType === 'Coverage Remove') {
                    $hasRecentCoverageReduction = true;
                }

                if ($activityType === 'Premium Change') {
                    $maxPremiumSpikePct = max($maxPremiumSpikePct, $this->calculatePremiumChangePct($activity));
                }
            }

            if ($daysAgo <= 90 && in_array($classification, ['Payment / billing', 'Complaint'], true)) {
                $hasRecentBillingIssue = true;
            }
        }

        $daysSinceMeaningfulActivity = $lastMeaningfulActivity
            ? (int) $lastMeaningfulActivity->diff($today)->format('%r%a')
            : null;

        return [
            'daysSinceMeaningfulActivity' => $daysSinceMeaningfulActivity,
            'hasRecentCancellationSignal' => $hasRecentCancellationSignal,
            'hasRecentCoverageReduction' => $hasRecentCoverageReduction,
            'hasRecentBillingIssue' => $hasRecentBillingIssue,
            'claimsCount3y' => $claimsCount3y,
            'maxPremiumSpikePct' => round($maxPremiumSpikePct, 2),
        ];
    }

    private function analyzeTasks(iterable $taskList, DateTimeImmutable $today): array
    {
        $openCount = 0;
        $openUrgentCount = 0;
        $overdueCount = 0;
        $overdueUrgentCount = 0;
        $openRescueCount = 0;

        foreach ($taskList as $task) {
            if ($this->isCrossSellPlaybookTask($task)) {
                continue;
            }

            $openCount++;
            $urgency = trim((string) ($task->get('urgency') ?? ''));
            $automationKey = trim((string) ($task->get('automationKey') ?? ''));

            if ($urgency === 'Urgent') {
                $openUrgentCount++;
            }

            if (str_starts_with($automationKey, 'activitylog-rescue:')) {
                $openRescueCount++;
            }

            $dueDate = $this->toDate((string) ($task->get('dateEndDate') ?? $task->get('dateEnd') ?? ''));
            if ($dueDate && $dueDate < $today) {
                $overdueCount++;
                if ($urgency === 'Urgent') {
                    $overdueUrgentCount++;
                }
            }
        }

        return [
            'openCount' => $openCount,
            'openUrgentCount' => $openUrgentCount,
            'overdueCount' => $overdueCount,
            'overdueUrgentCount' => $overdueUrgentCount,
            'openRescueCount' => $openRescueCount,
        ];
    }

    private function determineAccountStatus(
        array $policySignals,
        array $activitySignals,
        array $taskSignals,
        int $scoreTotal,
        float $rateIncreasePct
    ): string {
        if (
            $policySignals['hasCancellationStatus'] ||
            $activitySignals['hasRecentCancellationSignal'] ||
            $taskSignals['openUrgentCount'] > 0 ||
            $taskSignals['overdueUrgentCount'] > 0 ||
            $taskSignals['openRescueCount'] > 0 ||
            $policySignals['hasUrgentRenewal'] ||
            $rateIncreasePct >= 25.0
        ) {
            return 'Urgent';
        }

        if ($policySignals['hasRenewingMotion']) {
            return 'Renewing';
        }

        if (
            $policySignals['activePolicyCount'] === 0 &&
            $taskSignals['openCount'] === 0 &&
            (
                $activitySignals['daysSinceMeaningfulActivity'] === null ||
                $activitySignals['daysSinceMeaningfulActivity'] > 180
            )
        ) {
            return 'Inactive';
        }

        if (
            $policySignals['activePolicyCount'] === 0 ||
            $activitySignals['hasRecentCoverageReduction'] ||
            $rateIncreasePct >= 15.0 ||
            $taskSignals['overdueCount'] > 0 ||
            $scoreTotal < 65 ||
            (
                $activitySignals['daysSinceMeaningfulActivity'] !== null &&
                $activitySignals['daysSinceMeaningfulActivity'] > 90
            )
        ) {
            return 'At Risk';
        }

        return 'Active';
    }

    private function scoreBundleDepth(int $coverageDepth, int $gapCount): int
    {
        $baseScore = match (true) {
            $coverageDepth >= 4 => 20,
            $coverageDepth === 3 => 17,
            $coverageDepth === 2 => 14,
            $coverageDepth === 1 => 10,
            default => 0,
        };

        return max(0, $baseScore - min($gapCount, 4));
    }

    private function scorePaymentHistory(
        bool $hasRecentCancellationSignal,
        bool $hasRecentBillingIssue,
        int $overdueUrgentCount,
        int $openRescueCount,
        float $rateIncreasePct
    ): int {
        if ($hasRecentCancellationSignal || $openRescueCount > 0) {
            return 2;
        }

        if ($rateIncreasePct >= 25.0 || $overdueUrgentCount > 0) {
            return 6;
        }

        if ($hasRecentBillingIssue || $rateIncreasePct >= 15.0) {
            return 10;
        }

        return 20;
    }

    private function scoreYearsRetained(?DateTimeImmutable $oldestEffectiveDate, DateTimeImmutable $today): int
    {
        if (!$oldestEffectiveDate) {
            return 0;
        }

        $years = (float) $today->diff($oldestEffectiveDate)->days / 365;

        return match (true) {
            $years >= 7 => 20,
            $years >= 5 => 18,
            $years >= 3 => 15,
            $years >= 1 => 10,
            default => 6,
        };
    }

    private function scoreClaimsActivity(int $claimsCount3y): int
    {
        return match (true) {
            $claimsCount3y === 0 => 20,
            $claimsCount3y === 1 => 16,
            $claimsCount3y === 2 => 12,
            $claimsCount3y === 3 => 8,
            default => 4,
        };
    }

    private function scoreLastContact(?int $daysSinceMeaningfulActivity): int
    {
        if ($daysSinceMeaningfulActivity === null) {
            return 0;
        }

        return match (true) {
            $daysSinceMeaningfulActivity <= 14 => 20,
            $daysSinceMeaningfulActivity <= 30 => 16,
            $daysSinceMeaningfulActivity <= 60 => 12,
            $daysSinceMeaningfulActivity <= 90 => 8,
            $daysSinceMeaningfulActivity <= 180 => 4,
            default => 0,
        };
    }

    private function resolveScoreTier(int $scoreTotal): string
    {
        return match (true) {
            $scoreTotal >= 80 => 'Strong',
            $scoreTotal >= 65 => 'Good',
            $scoreTotal >= 45 => 'At Risk',
            default => 'Critical',
        };
    }

    private function resolveChangeDirection(int $scoreChangeAmount): string
    {
        return match (true) {
            $scoreChangeAmount > 0 => 'Up',
            $scoreChangeAmount < 0 => 'Down',
            default => 'Flat',
        };
    }

    private function countGapFlags(Entity $account): int
    {
        $count = 0;

        foreach (self::GAP_FIELDS as $field) {
            if ((bool) $account->get($field)) {
                $count++;
            }
        }

        return $count;
    }

    private function isCrossSellPlaybookTask(Entity $task): bool
    {
        return str_starts_with(trim((string) ($task->get('automationKey') ?? '')), 'account-playbook:');
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

    private function normalizeLine(string $value): string
    {
        return trim($value);
    }

    private function normalizeMultiEnum(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $value
        )));
    }

    private function extractCarrierValues(string $value): array
    {
        $parts = preg_split('/[;,|]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function toDate(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable(substr(str_replace('T', ' ', $value), 0, 10));
    }

    private function toDateTime(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable(substr(str_replace('T', ' ', $value), 0, 19));
    }
}
