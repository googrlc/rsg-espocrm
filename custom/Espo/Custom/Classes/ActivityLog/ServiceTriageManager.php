<?php

namespace Espo\Custom\Classes\ActivityLog;

use DateInterval;
use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class ServiceTriageManager
{
    private const AUTOMATION_KEY = 'activitylog:service-triage';

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function createTaskFromActivity(Entity $activityLog): void
    {
        $followUpTask = trim((string) ($activityLog->get('followUpTask') ?? ''));
        if ($followUpTask === '') {
            return;
        }

        $existingTaskList = $this->entityManager
            ->getRDBRepository('Task')
            ->where(['sourceActivityLogId' => $activityLog->getId()])
            ->find();

        foreach ($existingTaskList as $existingTask) {
            $automationKey = trim((string) ($existingTask->get('automationKey') ?? ''));
            if ($automationKey === '' || $automationKey === self::AUTOMATION_KEY) {
                return;
            }
        }

        $accountId = $activityLog->get('accountId');
        if (!$accountId) {
            return;
        }

        $policy = null;
        if ($activityLog->get('policyId')) {
            $policy = $this->entityManager->getEntityById('Policy', $activityLog->get('policyId'));
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);

        [$taskType, $urgency, $slaDays] = $this->resolveRouting($activityLog);
        [$assignedUserId, $assignedUserName, $teamsIds] = $this->resolveOwnership($activityLog, $policy, $account);
        [$parentType, $parentId, $parentName, $taskSource] = $this->resolveParent($activityLog, $policy);

        $task = $this->entityManager->getNewEntity('Task');
        $task->set([
            'name' => $followUpTask,
            'status' => 'Inbox',
            'taskType' => $taskType,
            'taskSource' => $taskSource,
            'syncSource' => 'Manual',
            'urgency' => $urgency,
            'triageSummary' => $this->buildTriageSummary($activityLog),
            'triageReason' => $this->buildTriageReason($activityLog, $taskType, $urgency, $slaDays),
            'description' => $this->buildDescription($activityLog),
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
            'dateEndDate' => $this->calculateDueDate($activityLog, $slaDays),
            'sourceActivityLogId' => $activityLog->getId(),
            'automationKey' => self::AUTOMATION_KEY,
        ]);

        $this->entityManager->saveEntity($task);
    }

    private function resolveRouting(Entity $activityLog): array
    {
        $classification = trim((string) ($activityLog->get('classification') ?? ''));
        $activityType = trim((string) ($activityLog->get('activityType') ?? ''));

        if ($activityType === 'Cancellation' || $classification === 'Cancellation / non-renewal notice') {
            return ['Policy Change', 'Urgent', 0];
        }

        if ($classification === 'Claim related') {
            return ['Claims', 'Urgent', 0];
        }

        if ($classification === 'Complaint') {
            return ['Client Service', 'Urgent', 0];
        }

        if (in_array($activityType, ['Endorsement', 'Premium Change', 'Coverage Add', 'Coverage Remove'], true)) {
            return ['Policy Change', 'High', 1];
        }

        if (in_array($classification, ['Coverage question', 'Carrier correspondence'], true)) {
            return ['Policy Change', 'High', 1];
        }

        if ($classification === 'Payment / billing') {
            return ['Client Service', 'High', 1];
        }

        if ($classification === 'Quote request') {
            return ['New Business', 'High', 1];
        }

        if ($classification === 'Renewal inquiry') {
            return ['Renewal', 'High', 1];
        }

        if ($classification === 'Document request') {
            return ['Client Service', 'Normal', 1];
        }

        if ($classification === 'Onboarding') {
            return ['Onboarding', 'Normal', 2];
        }

        if ($classification === 'Marketing outreach') {
            return ['Follow Up', 'Low', 3];
        }

        return ['Client Service', 'Normal', 2];
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

    private function buildTriageSummary(Entity $activityLog): string
    {
        $parts = array_filter([
            trim((string) ($activityLog->get('name') ?? '')),
            trim((string) ($activityLog->get('classification') ?? '')),
        ]);

        return implode(' | ', $parts);
    }

    private function buildTriageReason(Entity $activityLog, string $taskType, string $urgency, int $slaDays): string
    {
        $reasons = [];
        $reasons[] = 'Manual triage from Activity Log';
        $reasons[] = 'Activity Type: ' . trim((string) ($activityLog->get('activityType') ?? ''));

        if ($activityLog->get('classification')) {
            $reasons[] = 'Classification: ' . trim((string) ($activityLog->get('classification') ?? ''));
        }

        $reasons[] = 'Task Type: ' . $taskType;
        $reasons[] = 'Urgency: ' . $urgency;
        $reasons[] = 'SLA: ' . ($slaDays === 0 ? 'Today' : $slaDays . ' business day' . ($slaDays === 1 ? '' : 's'));

        return implode(' | ', $reasons);
    }

    private function buildDescription(Entity $activityLog): string
    {
        $lines = [];
        $lines[] = 'Source Activity Log: ' . $activityLog->getId();
        $lines[] = 'Subject: ' . trim((string) ($activityLog->get('name') ?? ''));
        $lines[] = 'Activity Type: ' . trim((string) ($activityLog->get('activityType') ?? ''));

        if ($activityLog->get('classification')) {
            $lines[] = 'Classification: ' . trim((string) ($activityLog->get('classification') ?? ''));
        }

        if ($activityLog->get('policyName')) {
            $lines[] = 'Policy: ' . trim((string) ($activityLog->get('policyName') ?? ''));
        }

        if ($activityLog->get('dateTime')) {
            $lines[] = 'Logged At: ' . substr(str_replace('T', ' ', (string) $activityLog->get('dateTime')), 0, 16);
        }

        $notes = trim((string) ($activityLog->get('notes') ?? ''));
        if ($notes !== '') {
            $lines[] = '';
            $lines[] = $notes;
        }

        return implode("\n", $lines);
    }

    private function calculateDueDate(Entity $activityLog, int $businessDays): string
    {
        $baseDateRaw = (string) ($activityLog->get('dateTime') ?: gmdate('Y-m-d H:i:s'));
        $baseDate = new DateTimeImmutable(substr(str_replace('T', ' ', $baseDateRaw), 0, 19));

        if ($businessDays === 0) {
            return $this->shiftWeekendToMonday($baseDate)->format('Y-m-d');
        }

        $date = $baseDate;
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
