<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Custom\Classes\Opportunity\ClosedLostRecycleWindows;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOption;
use Espo\ORM\Repository\Option\SaveOptions;

class RecycleClosedLostToLead implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $stage = (string) ($entity->get('stage') ?? '');
        $recycleToLead = (bool) ($entity->get('recycle_to_lead') ?? false);

        if ($stage !== 'Closed Lost' || !$recycleToLead) {
            return;
        }

        $xDate = trim((string) ($entity->get('x_date') ?? ''));
        if ($xDate === '') {
            return;
        }

        $lineOfBusiness = (string) ($entity->get('line_of_business') ?? '');
        $callbackDate = ClosedLostRecycleWindows::callbackDateFromXDate($xDate, $lineOfBusiness);
        if ($callbackDate === null) {
            return;
        }

        $lead = $this->findOrCreateLead($entity);
        $this->syncLeadFromOpportunity($lead, $entity, $xDate, $callbackDate);
        $this->entityManager->saveEntity($lead, [SaveOption::SILENT => true]);

        $this->syncFollowUpTask($lead, $entity, $callbackDate);
        $this->syncOpportunityLinks($entity, $lead, $callbackDate);
    }

    private function findOrCreateLead(Entity $opportunity): Entity
    {
        $existingLeadId = $opportunity->get('recycledLeadId');
        if ($existingLeadId) {
            $existingLead = $this->entityManager->getEntityById('Lead', $existingLeadId);
            if ($existingLead) {
                return $existingLead;
            }
        }

        $existingLead = $this->entityManager
            ->getRDBRepository('Lead')
            ->where(['sourceOpportunityId' => $opportunity->getId()])
            ->findOne();

        if ($existingLead) {
            return $existingLead;
        }

        return $this->entityManager->getNewEntity('Lead');
    }

    private function syncLeadFromOpportunity(
        Entity $lead,
        Entity $opportunity,
        string $xDate,
        string $callbackDate
    ): void {
        $opportunityName = trim((string) ($opportunity->get('name') ?? ''));
        $accountName = trim((string) ($opportunity->get('accountName') ?? ''));
        $lineOfBusiness = trim((string) ($opportunity->get('line_of_business') ?? ''));

        $leadName = $accountName !== ''
            ? sprintf('%s - Recycle Follow-up', $accountName)
            : ($opportunityName !== '' ? sprintf('%s - Recycle Follow-up', $opportunityName) : 'Recycle Follow-up');

        $insuranceInterest = $lineOfBusiness !== '' ? $lineOfBusiness : 'Other';

        $lead->set([
            'name' => $leadName,
            'status' => 'Nurture',
            'x_date' => $xDate,
            'callback_date' => $callbackDate,
            'sourceOpportunityId' => $opportunity->getId(),
            'sourceOpportunityName' => $opportunityName,
            'accountName' => $accountName !== '' ? $accountName : null,
            'assignedUserId' => $opportunity->get('assignedUserId'),
            'assignedUserName' => $opportunity->get('assignedUserName'),
            'teamsIds' => $opportunity->get('teamsIds') ?? [],
            'phoneNumber' => $opportunity->get('phoneNumber'),
            'emailAddress' => $opportunity->get('emailAddress'),
            'insurance_interest' => $insuranceInterest,
            'description' => $this->buildLeadDescription($opportunity, $callbackDate),
        ]);

        $priority = trim((string) ($lead->get('priority') ?? ''));
        if ($priority === '') {
            $lead->set('priority', 'Cold');
        }
    }

    private function buildLeadDescription(Entity $opportunity, string $callbackDate): string
    {
        $lines = [
            'Auto-generated from Closed Lost opportunity recycle.',
            'Original Opportunity: ' . (string) ($opportunity->get('name') ?? ''),
            'Line of Business: ' . (string) ($opportunity->get('line_of_business') ?? ''),
            'Renewal X-Date: ' . (string) ($opportunity->get('x_date') ?? ''),
            'Callback Date: ' . $callbackDate,
            'Lost Reason: ' . (string) ($opportunity->get('lost_reason') ?? ''),
        ];

        return implode("\n", array_filter($lines));
    }

    private function syncFollowUpTask(Entity $lead, Entity $opportunity, string $callbackDate): void
    {
        if (!$lead->hasId()) {
            return;
        }

        $automationKey = 'closed-lost-recycle:' . (string) $opportunity->getId();
        $task = $this->entityManager
            ->getRDBRepository('Task')
            ->where(['automationKey' => $automationKey])
            ->findOne();

        if (!$task) {
            $task = $this->entityManager->getNewEntity('Task');
        }

        $lineOfBusiness = trim((string) ($opportunity->get('line_of_business') ?? ''));
        $label = trim((string) ($opportunity->get('accountName') ?? $opportunity->get('name') ?? 'Lead'));

        $task->set([
            'name' => sprintf('Recycle Callback: %s (%s)', $label, $lineOfBusiness !== '' ? $lineOfBusiness : 'N/A'),
            'status' => 'Inbox',
            'taskType' => 'Follow Up',
            'urgency' => $this->resolveTaskUrgency($callbackDate),
            'dateEndDate' => $callbackDate,
            'parentType' => 'Lead',
            'parentId' => $lead->getId(),
            'parentName' => $lead->get('name'),
            'assignedUserId' => $lead->get('assignedUserId'),
            'assignedUserName' => $lead->get('assignedUserName'),
            'teamsIds' => $lead->get('teamsIds') ?? [],
            'accountId' => $opportunity->get('accountId'),
            'accountName' => $opportunity->get('accountName'),
            'linkedAccountId' => $opportunity->get('accountId'),
            'linkedAccountName' => $opportunity->get('accountName'),
            'description' => 'Follow up for recycled Closed Lost opportunity at callback window.',
            'automationKey' => $automationKey,
        ]);

        $this->entityManager->saveEntity($task, [SaveOption::SILENT => true]);
    }

    private function resolveTaskUrgency(string $callbackDate): string
    {
        $today = date('Y-m-d');

        if ($callbackDate <= $today) {
            return 'High';
        }

        return 'Normal';
    }

    private function syncOpportunityLinks(Entity $opportunity, Entity $lead, string $callbackDate): void
    {
        if (!$lead->hasId()) {
            return;
        }

        $hasChanges = false;

        if ((string) ($opportunity->get('callback_date') ?? '') !== $callbackDate) {
            $opportunity->set('callback_date', $callbackDate);
            $hasChanges = true;
        }

        if ((string) ($opportunity->get('recycledLeadId') ?? '') !== (string) $lead->getId()) {
            $opportunity->set('recycledLeadId', $lead->getId());
            $opportunity->set('recycledLeadName', $lead->get('name'));
            $hasChanges = true;
        }

        if (!$hasChanges) {
            return;
        }

        $this->entityManager->saveEntity($opportunity, [SaveOption::SILENT => true]);
    }
}
