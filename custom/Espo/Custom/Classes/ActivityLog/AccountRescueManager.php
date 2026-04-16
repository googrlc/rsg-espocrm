<?php

namespace Espo\Custom\Classes\ActivityLog;

use DateInterval;
use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AccountRescueManager
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function createTaskFromActivity(Entity $activityLog): void
    {
        $accountId = $activityLog->get('accountId');
        if (!$accountId) {
            return;
        }

        $rule = $this->resolveRule($activityLog);
        if ($rule === null) {
            return;
        }

        $existingTask = $this->entityManager
            ->getRDBRepository('Task')
            ->where([
                'sourceActivityLogId' => $activityLog->getId(),
                'automationKey' => $rule['automationKey'],
                'status!=' => ['Completed', 'Cancelled'],
            ])
            ->findOne();

        if ($existingTask) {
            return;
        }

        $policy = null;
        if ($activityLog->get('policyId')) {
            $policy = $this->entityManager->getEntityById('Policy', $activityLog->get('policyId'));
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);
        [$assignedUserId, $assignedUserName, $teamsIds] = $this->resolveOwnership($activityLog, $policy, $account);
        [$parentType, $parentId, $parentName, $taskSource] = $this->resolveParent($activityLog, $policy);

        $task = $this->entityManager->getNewEntity('Task');
        $task->set([
            'name' => $this->buildTaskName($activityLog, $rule),
            'status' => 'Inbox',
            'taskType' => 'Policy Change',
            'taskSource' => $taskSource,
            'syncSource' => $this->resolveSyncSource($activityLog),
            'urgency' => $rule['urgency'],
            'triageSummary' => $this->buildSummary($activityLog, $rule),
            'triageReason' => $this->buildReason($activityLog, $rule),
            'description' => $this->buildDescription($activityLog, $rule),
            'linkedAccountId' => $activityLog->get('accountId'),
            'linkedAccountName' => $activityLog->get('accountName'),
            'accountId' => $activityLog->get('accountId'),
            'accountName' => $activityLog->get('accountName'),
            'contactId' => $activityLog->get('contactId'),
            'contactName' => $activityLog->get('contactName'),
            'assignedUserId' => $assignedUserId,
            'assignedUserName' => $assignedUserName,
            'teamsIds' => $teamsIds,
            'parentType' => $parentType,
            'parentId' => $parentId,
            'parentName' => $parentName,
            'dateEndDate' => $this->calculateDueDate((int) $rule['slaDays']),
            'sourceActivityLogId' => $activityLog->getId(),
            'automationKey' => $rule['automationKey'],
        ]);

        $this->entityManager->saveEntity($task);
    }

    private function resolveRule(Entity $activityLog): ?array
    {
        $activityType = trim((string) ($activityLog->get('activityType') ?? ''));
        $classification = trim((string) ($activityLog->get('classification') ?? ''));

        if ($activityType === 'Cancellation' || $classification === 'Cancellation / non-renewal notice') {
            return [
                'automationKey' => 'activitylog-rescue:cancellation',
                'label' => 'Cancellation Save Motion',
                'urgency' => 'Urgent',
                'slaDays' => 0,
            ];
        }

        if ($activityType === 'Coverage Remove') {
            return [
                'automationKey' => 'activitylog-rescue:coverage-remove',
                'label' => 'Coverage Rescue Review',
                'urgency' => 'High',
                'slaDays' => 1,
            ];
        }

        if ($activityType !== 'Premium Change') {
            return null;
        }

        $premiumChangePct = $this->calculatePremiumChangePct($activityLog);
        if ($premiumChangePct < 15.0) {
            return null;
        }

        return [
            'automationKey' => 'activitylog-rescue:premium-spike',
            'label' => 'Rate Rescue Review',
            'urgency' => $premiumChangePct >= 25.0 ? 'Urgent' : 'High',
            'slaDays' => $premiumChangePct >= 25.0 ? 0 : 1,
            'premiumChangePct' => round($premiumChangePct, 2),
        ];
    }

    private function resolveOwnership(Entity $activityLog, ?Entity $policy, ?Entity $account): array
    {
        $assignedUserId = $activityLog->get('assignedUserId');
        $assignedUserName = $activityLog->get('assignedUserName');
        $teamsIds = $activityLog->get('teamsIds') ?? [];

        if (!$assignedUserId && $policy) {
            $assignedUserId = $policy->get('assignedUserId');
            $assignedUserName = $policy->get('assignedUserName');
            $teamsIds = $teamsIds ?: ($policy->get('teamsIds') ?? []);
        }

        if (!$assignedUserId && $account) {
            $assignedUserId = $account->get('assignedUserId');
            $assignedUserName = $account->get('assignedUserName');
            $teamsIds = $teamsIds ?: ($account->get('teamsIds') ?? []);
        }

        return [$assignedUserId, $assignedUserName, $teamsIds];
    }

    private function resolveParent(Entity $activityLog, ?Entity $policy): array
    {
        if ($activityLog->get('policyId')) {
            return [
                'Policy',
                $activityLog->get('policyId'),
                $activityLog->get('policyName') ?: ($policy?->get('name') ?? ''),
                'Policy',
            ];
        }

        if ($activityLog->get('contactId')) {
            return [
                'Contact',
                $activityLog->get('contactId'),
                $activityLog->get('contactName'),
                'Contact',
            ];
        }

        return [
            'Account',
            $activityLog->get('accountId'),
            $activityLog->get('accountName'),
            'Account',
        ];
    }

    private function buildTaskName(Entity $activityLog, array $rule): string
    {
        $accountName = trim((string) ($activityLog->get('accountName') ?? 'Account'));
        $policyName = trim((string) ($activityLog->get('policyName') ?? ''));

        if ($policyName !== '') {
            return $rule['label'] . ': ' . $policyName;
        }

        return $rule['label'] . ': ' . $accountName;
    }

    private function buildSummary(Entity $activityLog, array $rule): string
    {
        $parts = [$rule['label']];

        $changeSummary = trim((string) ($activityLog->get('changeSummary') ?? ''));
        if ($changeSummary !== '') {
            $parts[] = $changeSummary;
        }

        return implode(' | ', $parts);
    }

    private function buildReason(Entity $activityLog, array $rule): string
    {
        $parts = [];
        $parts[] = 'Automatic rescue task from Activity Log';
        $parts[] = 'Activity Type: ' . trim((string) ($activityLog->get('activityType') ?? ''));

        if (!empty($rule['premiumChangePct'])) {
            $parts[] = 'Premium Change: ' . number_format((float) $rule['premiumChangePct'], 2) . '%';
        }

        $changeSummary = trim((string) ($activityLog->get('changeSummary') ?? ''));
        if ($changeSummary !== '') {
            $parts[] = 'Change: ' . $changeSummary;
        }

        $parts[] = 'SLA: ' . ((int) $rule['slaDays'] === 0 ? 'Today' : $rule['slaDays'] . ' business day');

        return implode(' | ', $parts);
    }

    private function buildDescription(Entity $activityLog, array $rule): string
    {
        $lines = [];
        $lines[] = 'Source Activity Log: ' . $activityLog->getId();
        $lines[] = 'Playbook: ' . $rule['label'];
        $lines[] = 'Subject: ' . trim((string) ($activityLog->get('name') ?? ''));
        $lines[] = 'Activity Type: ' . trim((string) ($activityLog->get('activityType') ?? ''));

        if ($activityLog->get('policyName')) {
            $lines[] = 'Policy: ' . trim((string) ($activityLog->get('policyName') ?? ''));
        }

        $changeSummary = trim((string) ($activityLog->get('changeSummary') ?? ''));
        if ($changeSummary !== '') {
            $lines[] = 'Change Summary: ' . $changeSummary;
        }

        $oldPremium = $activityLog->get('oldPremium');
        $newPremium = $activityLog->get('newPremium');
        if ($oldPremium !== null && $oldPremium !== '') {
            $lines[] = 'Old Premium: ' . $oldPremium;
        }
        if ($newPremium !== null && $newPremium !== '') {
            $lines[] = 'New Premium: ' . $newPremium;
        }

        if (!empty($rule['premiumChangePct'])) {
            $lines[] = 'Premium Change %: ' . number_format((float) $rule['premiumChangePct'], 2);
        }

        $notes = trim((string) ($activityLog->get('notes') ?? ''));
        if ($notes !== '') {
            $lines[] = '';
            $lines[] = $notes;
        }

        return implode("\n", $lines);
    }

    private function resolveSyncSource(Entity $activityLog): string
    {
        return match (trim((string) ($activityLog->get('source') ?? ''))) {
            'NowCerts Sync' => 'Momentum',
            'n8n Automated' => 'n8n',
            default => 'Manual',
        };
    }

    private function calculatePremiumChangePct(Entity $activityLog): float
    {
        $oldPremium = (float) ($activityLog->get('oldPremium') ?? 0);
        $newPremium = (float) ($activityLog->get('newPremium') ?? 0);
        $premiumDelta = $activityLog->get('premiumDelta');
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

    private function calculateDueDate(int $businessDays): string
    {
        $date = new DateTimeImmutable('today');
        if ($businessDays === 0) {
            return $this->shiftWeekendToMonday($date)->format('Y-m-d');
        }

        $addedDays = 0;
        while ($addedDays < $businessDays) {
            $date = $date->add(new DateInterval('P1D'));
            if ((int) $date->format('N') < 6) {
                $addedDays++;
            }
        }

        return $date->format('Y-m-d');
    }

    private function shiftWeekendToMonday(DateTimeImmutable $date): DateTimeImmutable
    {
        while ((int) $date->format('N') > 5) {
            $date = $date->add(new DateInterval('P1D'));
        }

        return $date;
    }
}
