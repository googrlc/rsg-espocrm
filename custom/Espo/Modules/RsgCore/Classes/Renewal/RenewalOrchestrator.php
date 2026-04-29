<?php

namespace Espo\Modules\RsgCore\Classes\Renewal;

use DateInterval;
use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Account\AccountNameResolution;
use Espo\Custom\Classes\Renewal\RenewalLeadWindows;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class RenewalOrchestrator
{
    private const POLICY_SYNC_STATUSES = [
        'Active',
        'Up for Renewal',
        'Renewing',
    ];

    private const FINAL_RENEWAL_STAGES = [
        'Renewed - Won',
        'Lost',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function syncFromPolicy(Entity $policy): void
    {
        if (!$this->shouldSyncRenewalFromPolicy($policy)) {
            return;
        }

        $existingRenewal = $this->findRenewalByPolicyId((string) $policy->getId());
        if ($existingRenewal === null && !$this->isWithinRenewalCreationWindow($policy)) {
            return;
        }

        $renewal = $existingRenewal ?? $this->entityManager->getNewEntity('Renewal');
        $originalExpirationDate = (string) ($renewal->get('expiration_date') ?? '');
        $originalUrgency = (string) ($renewal->get('urgency') ?? '');
        $renewalIsNew = !$renewal->hasId();
        $hasChanges = false;

        $expirationDate = (string) ($policy->get('expiration_date') ?? '');
        $lobSourceRaw = trim((string) ($policy->get('line_of_business_raw') ?? ''));
        if ($lobSourceRaw === '') {
            $lobSourceRaw = trim((string) ($policy->get('line_of_business') ?? $policy->get('business_type') ?? ''));
        }
        $normalizedLineOfBusiness = $this->normalizeLineOfBusiness($lobSourceRaw);
        $resolvedAccountName = AccountNameResolution::resolveForPolicy($this->entityManager, $policy);
        $renewalAccountName = $resolvedAccountName !== ''
            ? $resolvedAccountName
            : trim((string) ($policy->get('accountName') ?? ''));

        $hasChanges = $this->setIfChanged($renewal, 'name', $this->buildRenewalName(
            $resolvedAccountName,
            $normalizedLineOfBusiness
        )) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'policyId', $policy->getId()) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'policyName', $policy->get('name')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'accountId', $policy->get('accountId')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'accountName', $renewalAccountName) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'contactId', $policy->get('contactId')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'contactName', $policy->get('contactName')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'assignedUserId', $policy->get('assignedUserId')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'assignedUserName', $policy->get('assignedUserName')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'teamsIds', $policy->get('teamsIds') ?? []) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'expiration_date', $expirationDate) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'current_premium', $policy->get('premium_amount')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'line_of_business', $normalizedLineOfBusiness) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'carrier', $policy->get('carrier')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'commission_rate', $this->normalizeRate($policy->get('commission_rate'))) || $hasChanges;

        if ($this->shouldSyncRenewalEffectiveDate($renewal, $originalExpirationDate, $expirationDate)) {
            $hasChanges = $this->setIfChanged($renewal, 'renewal_effective_date', $expirationDate) || $hasChanges;
        }

        $computedUrgency = $this->calculateUrgency($expirationDate);
        if ($this->shouldSyncRenewalUrgency($originalUrgency, $originalExpirationDate) && $computedUrgency !== null) {
            $hasChanges = $this->setIfChanged($renewal, 'urgency', $computedUrgency) || $hasChanges;
        }

        if ((string) ($renewal->get('stage') ?? '') === '') {
            $renewal->set('stage', 'Identified');
            $hasChanges = true;
        }

        if ($renewalIsNew || $hasChanges) {
            $this->entityManager->saveEntity($renewal, [SaveOption::SILENT => true]);
        }

        if ($this->shouldCreateInitialTask($policy, $renewal)) {
            $this->createInitialTaskIfMissing($policy, $renewal);
        }
    }

    public function applyDerivedFields(Entity $renewal): void
    {
        $raw = trim((string) ($renewal->get('accountName') ?? ''));
        $resolved = AccountNameResolution::resolveForRenewal($this->entityManager, $renewal);
        if ($resolved !== '' && ($raw === '' || AccountNameResolution::isPlaceholder($raw))) {
            $renewal->set('accountName', $resolved);
        }

        $accountName = trim((string) ($renewal->get('accountName') ?? ''));
        $lineOfBusiness = $this->normalizeLineOfBusiness($renewal->get('line_of_business'));
        $renewal->set('name', $this->buildRenewalName($accountName !== '' ? $accountName : 'Account', $lineOfBusiness));

        $expirationDate = (string) ($renewal->get('expiration_date') ?? '');
        $fetchedExpirationDate = (string) ($renewal->getFetched('expiration_date') ?? '');
        $effectiveDate = (string) ($renewal->get('renewal_effective_date') ?? '');
        $fetchedEffectiveDate = (string) ($renewal->getFetched('renewal_effective_date') ?? '');

        if (
            $expirationDate !== '' &&
            (
                $effectiveDate === '' ||
                (
                    $fetchedEffectiveDate !== '' &&
                    $fetchedEffectiveDate === $fetchedExpirationDate &&
                    $effectiveDate === $fetchedEffectiveDate
                )
            )
        ) {
            $renewal->set('renewal_effective_date', $expirationDate);
        }

        $renewalPremium = $renewal->get('renewal_premium');
        $currentPremium = (float) ($renewal->get('current_premium') ?? 0);
        if ($renewalPremium !== null && $renewalPremium !== '' && $currentPremium > 0) {
            $premiumChange = (((float) $renewalPremium - $currentPremium) / $currentPremium) * 100;
            $renewal->set('premium_change', round($premiumChange, 2));
        } else {
            $renewal->set('premium_change', null);
        }

        if ($renewalPremium !== null && $renewalPremium !== '') {
            $expectedCommission = (float) $renewalPremium * $this->normalizeRate($renewal->get('commission_rate'));
            $renewal->set('expected_commission', round($expectedCommission, 2));
        } else {
            $renewal->set('expected_commission', null);
        }

        $currentUrgency = (string) ($renewal->get('urgency') ?? '');
        $fetchedUrgency = (string) ($renewal->getFetched('urgency') ?? '');
        $fetchedCalculatedUrgency = $this->calculateUrgency($fetchedExpirationDate);
        $calculatedUrgency = $this->calculateUrgency($expirationDate);

        if (
            $calculatedUrgency !== null &&
            (
                $currentUrgency === '' ||
                (
                    $currentUrgency === $fetchedUrgency &&
                    ($fetchedUrgency === '' || $fetchedUrgency === $fetchedCalculatedUrgency)
                )
            )
        ) {
            $renewal->set('urgency', $calculatedUrgency);
        }
    }

    public function syncPolicyFromRenewal(Entity $renewal): void
    {
        $policyId = $renewal->get('policyId');
        if (!$policyId) {
            return;
        }

        $policy = $this->entityManager->getEntityById('Policy', $policyId);
        if (!$policy) {
            return;
        }

        $targetStatus = $this->mapRenewalStageToPolicyStatus(
            $policy,
            (string) ($renewal->get('stage') ?? '')
        );

        if ($targetStatus === null || $targetStatus === (string) ($policy->get('status') ?? '')) {
            return;
        }

        $policy->set('status', $targetStatus);
        $this->entityManager->saveEntity($policy, [SaveOption::SILENT => true]);
    }

    private function shouldSyncRenewalFromPolicy(Entity $policy): bool
    {
        return
            (bool) $policy->get('accountId') &&
            (bool) $policy->get('expiration_date') &&
            in_array((string) ($policy->get('status') ?? ''), self::POLICY_SYNC_STATUSES, true);
    }

    /**
     * New renewals only once expiration is within the LOB lead window (commercial 90d, personal 30d).
     */
    private function isWithinRenewalCreationWindow(Entity $policy): bool
    {
        $daysRemaining = $this->calculateDaysRemaining((string) ($policy->get('expiration_date') ?? ''));
        if ($daysRemaining === null || $daysRemaining < 0) {
            return false;
        }

        $window = RenewalLeadWindows::leadDaysForPolicy($policy);

        return $daysRemaining <= $window;
    }

    private function shouldCreateInitialTask(Entity $policy, Entity $renewal): bool
    {
        if (!$renewal->hasId()) {
            return false;
        }

        if (in_array((string) ($renewal->get('stage') ?? ''), self::FINAL_RENEWAL_STAGES, true)) {
            return false;
        }

        $daysRemaining = $this->calculateDaysRemaining((string) ($policy->get('expiration_date') ?? ''));
        $window = RenewalLeadWindows::leadDaysForPolicy($policy);

        return $daysRemaining !== null && $daysRemaining <= $window;
    }

    private function createInitialTaskIfMissing(Entity $policy, Entity $renewal): void
    {
        $existingTask = $this->entityManager
            ->getRDBRepository('Task')
            ->where([
                'parentId' => $renewal->getId(),
                'parentType' => 'Renewal',
                'taskType' => 'Renewal',
            ])
            ->findOne();

        if ($existingTask) {
            return;
        }

        $task = $this->entityManager->getNewEntity('Task');
        $expirationDate = (string) ($policy->get('expiration_date') ?? '');
        $lobSource = trim((string) ($policy->get('line_of_business_raw') ?? ''));
        if ($lobSource === '') {
            $lobSource = trim((string) ($policy->get('line_of_business') ?? $policy->get('business_type') ?? ''));
        }
        $lineOfBusiness = $this->normalizeLineOfBusiness($lobSource);
        $taskAccountName = AccountNameResolution::resolveForPolicy($this->entityManager, $policy);
        if ($taskAccountName === '') {
            $taskAccountName = trim((string) ($policy->get('accountName') ?? '')) ?: 'Account';
        }

        $task->set([
            'name' => sprintf(
                'Renewal Review: %s - %s',
                $taskAccountName,
                $lineOfBusiness
            ),
            'status' => 'Inbox',
            'taskType' => 'Renewal',
            'taskSource' => 'Policy',
            'parentId' => $renewal->getId(),
            'parentType' => 'Renewal',
            'parentName' => $renewal->get('name'),
            'linkedAccountId' => $policy->get('accountId'),
            'linkedAccountName' => $taskAccountName,
            'accountId' => $policy->get('accountId'),
            'accountName' => $taskAccountName,
            'contactId' => $policy->get('contactId'),
            'contactName' => $policy->get('contactName'),
            'assignedUserId' => $renewal->get('assignedUserId'),
            'assignedUserName' => $renewal->get('assignedUserName'),
            'teamsIds' => $renewal->get('teamsIds') ?? [],
            'urgency' => $this->mapTaskUrgency($expirationDate),
            'dateEndDate' => $this->calculateInitialTaskDueDate($expirationDate),
            'description' => $this->buildTaskDescription($policy),
        ]);

        $this->entityManager->saveEntity($task, [SaveOption::SILENT => true]);
    }

    private function buildTaskDescription(Entity $policy): string
    {
        $lines = [];
        $lines[] = 'Expiring Policy: ' . (string) ($policy->get('name') ?? '');
        $lines[] = 'Carrier: ' . (string) ($policy->get('carrier') ?? '');
        $lines[] = 'Expiration Date: ' . (string) ($policy->get('expiration_date') ?? '');
        $lines[] = 'Current Premium: ' . (string) ($policy->get('premium_amount') ?? '');

        return implode("\n", array_filter($lines));
    }

    private function calculateInitialTaskDueDate(string $expirationDate): ?string
    {
        if ($expirationDate === '') {
            return null;
        }

        $expiration = new DateTimeImmutable($expirationDate);
        $target = $expiration->sub(new DateInterval('P45D'));
        $today = new DateTimeImmutable('today');

        if ($target < $today) {
            $target = $today;
        }

        return $target->format('Y-m-d');
    }

    private function mapTaskUrgency(string $expirationDate): string
    {
        return match ($this->calculateUrgency($expirationDate)) {
            'Critical' => 'Urgent',
            'High' => 'High',
            'Medium' => 'Normal',
            default => 'Low',
        };
    }

    private function mapRenewalStageToPolicyStatus(Entity $policy, string $stage): ?string
    {
        $expirationDate = (string) ($policy->get('expiration_date') ?? '');
        $currentPolicyStatus = (string) ($policy->get('status') ?? '');
        $daysRemaining = $this->calculateDaysRemaining($expirationDate);
        $window = RenewalLeadWindows::leadDaysForPolicy($policy);

        return match ($stage) {
            'Identified' => $daysRemaining !== null && $daysRemaining <= $window
                ? 'Up for Renewal'
                : ($currentPolicyStatus === 'Renewing' ? 'Up for Renewal' : null),
            'Outreach Sent', 'Quote Requested', 'Proposal Sent', 'Negotiating' => 'Renewing',
            'Renewed - Won' => 'Renewed',
            'Lost' => 'Non-Renewed',
            default => null,
        };
    }

    private function shouldSyncRenewalEffectiveDate(Entity $renewal, string $originalExpirationDate, string $newExpirationDate): bool
    {
        $currentEffectiveDate = (string) ($renewal->get('renewal_effective_date') ?? '');

        return
            $newExpirationDate !== '' &&
            (
                $currentEffectiveDate === '' ||
                $currentEffectiveDate === $originalExpirationDate
            );
    }

    private function shouldSyncRenewalUrgency(string $originalUrgency, string $originalExpirationDate): bool
    {
        $calculatedOriginalUrgency = $this->calculateUrgency($originalExpirationDate);

        return $originalUrgency === '' || $originalUrgency === $calculatedOriginalUrgency;
    }

    private function findRenewalByPolicyId(string $policyId): ?Entity
    {
        return $this->entityManager
            ->getRDBRepository('Renewal')
            ->where(['policyId' => $policyId])
            ->findOne();
    }

    private function buildRenewalName(string $accountName, string $lineOfBusiness): string
    {
        $accountName = trim($accountName) !== '' ? trim($accountName) : 'Account';
        $lineOfBusiness = trim($lineOfBusiness) !== '' ? trim($lineOfBusiness) : 'Policy';

        return $accountName . ' - ' . $lineOfBusiness . ' Renewal';
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

    private function normalizeRate(mixed $rate): float
    {
        if ($rate === null || $rate === '') {
            return 0.12;
        }

        $numericRate = (float) $rate;

        return $numericRate > 1 ? $numericRate / 100 : $numericRate;
    }

    private function calculateDaysRemaining(string $expirationDate): ?int
    {
        if ($expirationDate === '') {
            return null;
        }

        $today = new DateTimeImmutable('today');
        $expiration = new DateTimeImmutable($expirationDate);

        return (int) $today->diff($expiration)->format('%r%a');
    }

    private function calculateUrgency(string $expirationDate): ?string
    {
        $daysRemaining = $this->calculateDaysRemaining($expirationDate);

        if ($daysRemaining === null) {
            return null;
        }

        return match (true) {
            $daysRemaining <= 30 => 'Critical',
            $daysRemaining <= 60 => 'High',
            $daysRemaining <= 90 => 'Medium',
            default => 'Low',
        };
    }

    private function setIfChanged(Entity $entity, string $field, mixed $value): bool
    {
        $currentValue = $entity->get($field);

        if (is_array($currentValue) || is_array($value)) {
            $current = $currentValue ?? [];
            $next = $value ?? [];
            sort($current);
            sort($next);

            if ($current === $next) {
                return false;
            }
        } elseif ($currentValue === $value) {
            return false;
        }

        $entity->set($field, $value);

        return true;
    }
}
