<?php

namespace Espo\Custom\Classes\Renewal;

use DateInterval;
use DateTimeImmutable;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Account\AccountNameResolution;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class RenewalOrchestrator
{
    private const RENEWAL_WINDOW_DAYS = 60;

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

        $renewal = $this->findRenewalByPolicyId((string) $policy->getId()) ?? $this->entityManager->getNewEntity('Renewal');
        $originalExpirationDate = (string) ($renewal->get('expirationDate') ?? '');
        $originalUrgency = (string) ($renewal->get('urgency') ?? '');
        $renewalIsNew = !$renewal->getId();
        $hasChanges = false;

        $expirationDate = (string) ($policy->get('expirationDate') ?? '');
        $normalizedLineOfBusiness = $this->normalizeLineOfBusiness($policy->get('lineOfBusiness') ?? $policy->get('businessType'));
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
        $hasChanges = $this->setIfChanged($renewal, 'expirationDate', $expirationDate) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'currentPremium', $policy->get('premiumAmount')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'lineOfBusiness', $normalizedLineOfBusiness) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'carrier', $policy->get('carrier')) || $hasChanges;
        $hasChanges = $this->setIfChanged($renewal, 'commissionRate', $this->normalizeRate($policy->get('commissionRate'))) || $hasChanges;

        if ($this->shouldSyncRenewalEffectiveDate($renewal, $originalExpirationDate, $expirationDate)) {
            $hasChanges = $this->setIfChanged($renewal, 'renewalEffectiveDate', $expirationDate) || $hasChanges;
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
        $lineOfBusiness = $this->normalizeLineOfBusiness($renewal->get('lineOfBusiness'));
        $renewal->set('name', $this->buildRenewalName($accountName !== '' ? $accountName : 'Account', $lineOfBusiness));

        $expirationDate = (string) ($renewal->get('expirationDate') ?? '');
        $fetchedExpirationDate = (string) ($renewal->getFetched('expirationDate') ?? '');
        $effectiveDate = (string) ($renewal->get('renewalEffectiveDate') ?? '');
        $fetchedEffectiveDate = (string) ($renewal->getFetched('renewalEffectiveDate') ?? '');

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
            $renewal->set('renewalEffectiveDate', $expirationDate);
        }

        $renewalPremium = $renewal->get('renewalPremium');
        $currentPremium = (float) ($renewal->get('currentPremium') ?? 0);
        if ($renewalPremium !== null && $renewalPremium !== '' && $currentPremium > 0) {
            $premiumChange = (((float) $renewalPremium - $currentPremium) / $currentPremium) * 100;
            $renewal->set('premiumChange', round($premiumChange, 2));
        } else {
            $renewal->set('premiumChange', null);
        }

        if ($renewalPremium !== null && $renewalPremium !== '') {
            $expectedCommission = (float) $renewalPremium * $this->normalizeRate($renewal->get('commissionRate'));
            $renewal->set('expectedCommission', round($expectedCommission, 2));
        } else {
            $renewal->set('expectedCommission', null);
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
            (string) ($renewal->get('stage') ?? ''),
            (string) ($policy->get('expirationDate') ?? ''),
            (string) ($policy->get('status') ?? '')
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
            (bool) $policy->get('expirationDate') &&
            in_array((string) ($policy->get('status') ?? ''), self::POLICY_SYNC_STATUSES, true);
    }

    private function shouldCreateInitialTask(Entity $policy, Entity $renewal): bool
    {
        if (!$renewal->getId()) {
            return false;
        }

        if (in_array((string) ($renewal->get('stage') ?? ''), self::FINAL_RENEWAL_STAGES, true)) {
            return false;
        }

        $daysRemaining = $this->calculateDaysRemaining((string) ($policy->get('expirationDate') ?? ''));

        return $daysRemaining !== null && $daysRemaining <= self::RENEWAL_WINDOW_DAYS;
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
        $expirationDate = (string) ($policy->get('expirationDate') ?? '');
        $lineOfBusiness = $this->normalizeLineOfBusiness($policy->get('lineOfBusiness') ?? $policy->get('businessType'));
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
        $lines[] = 'Expiration Date: ' . (string) ($policy->get('expirationDate') ?? '');
        $lines[] = 'Current Premium: ' . (string) ($policy->get('premiumAmount') ?? '');

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

    private function mapRenewalStageToPolicyStatus(string $stage, string $expirationDate, string $currentPolicyStatus): ?string
    {
        return match ($stage) {
            'Identified' => $this->calculateDaysRemaining($expirationDate) !== null
                && $this->calculateDaysRemaining($expirationDate) <= self::RENEWAL_WINDOW_DAYS
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
        $currentEffectiveDate = (string) ($renewal->get('renewalEffectiveDate') ?? '');

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
