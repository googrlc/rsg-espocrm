<?php

namespace Espo\Custom\Hooks\Lead;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOption;
use Espo\ORM\Repository\Option\SaveOptions;

class SyncConvertedRecords implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $status = (string) ($entity->get('status') ?? '');
        $fetchedStatus = (string) ($entity->getFetched('status') ?? '');

        if ($status !== 'Converted' || $fetchedStatus === 'Converted') {
            return;
        }

        $accountId = $entity->get('createdAccountId');
        $contactId = $entity->get('createdContactId');

        if ($contactId) {
            $contact = $this->entityManager->getEntityById('Contact', $contactId);

            if ($contact) {
                $this->syncContact($entity, $contact, $accountId);
            }
        }

        if ($accountId) {
            $account = $this->entityManager->getEntityById('Account', $accountId);

            if ($account) {
                $this->syncAccount($entity, $account);
            }

            $this->ensureOpportunityFromLead($entity, $accountId);
        }
    }

    private function ensureOpportunityFromLead(Entity $lead, string $accountId): void
    {
        if ($lead->get('createdOpportunityId')) {
            return;
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);
        if (!$account) {
            return;
        }

        $name = trim((string) ($lead->get('name') ?? ''));
        if ($name === '') {
            $name = 'New Opportunity';
        }

        $lineOfBusiness = $this->resolveOpportunityLineOfBusiness($lead);
        $existingOpportunity = $this->entityManager
            ->getRDBRepository('Opportunity')
            ->where([
                'accountId' => $accountId,
                'lineOfBusiness' => $lineOfBusiness,
                'businessType' => 'New Business',
                'stage!=' => ['Closed Won', 'Closed Lost', 'Bound / Renewed', 'Non-Renewal / Lost'],
            ])
            ->findOne();

        if ($existingOpportunity) {
            $lead->set('createdOpportunityId', $existingOpportunity->getId());
            $this->entityManager->saveEntity($lead, [SaveOption::SILENT => true]);

            return;
        }

        $opportunity = $this->entityManager->getNewEntity('Opportunity');
        $opportunity->set([
            'name' => $name,
            'accountId' => $accountId,
            'accountName' => $account->get('name'),
            'stage' => 'Discovery',
            'lineOfBusiness' => $lineOfBusiness,
            'businessType' => 'New Business',
            'leadSource' => $lead->get('source') ?: $lead->get('campaignName'),
            'currentCarrier' => $lead->get('currentCarrier'),
            'estimatedPremium' => $lead->get('estimatedPremium'),
            'priority' => $lead->get('priority'),
            'description' => $lead->get('description'),
            'assignedUserId' => $lead->get('assignedUserId'),
            'assignedUserName' => $lead->get('assignedUserName'),
        ]);

        $this->entityManager->saveEntity($opportunity, [SaveOption::SILENT => true]);

        $lead->set('createdOpportunityId', $opportunity->getId());
        $this->entityManager->saveEntity($lead, [SaveOption::SILENT => true]);
    }

    private function resolveOpportunityLineOfBusiness(Entity $lead): string
    {
        $interest = trim((string) ($lead->get('insuranceInterest') ?? ''));

        return match ($interest) {
            '', 'Multiple' => 'Other',
            default => $interest,
        };
    }

    private function syncContact(Entity $lead, Entity $contact, ?string $accountId): void
    {
        $hasChanges = false;

        $hasChanges = $this->setIfEmpty($contact, 'dateOfBirth', $lead->get('dateOfBirth')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($contact, 'phoneNumber', $lead->get('phoneNumber')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($contact, 'emailAddress', $lead->get('emailAddress')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($contact, 'originalLeadSource', $lead->get('source') ?: $lead->get('campaignName')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($contact, 'contactType', 'Prospect') || $hasChanges;
        $hasChanges = $this->setIfEmpty($contact, 'relationshipToAccount', 'Head of Household') || $hasChanges;

        if ($accountId && !$contact->get('accountId')) {
            $account = $this->entityManager->getEntityById('Account', $accountId);

            if ($account) {
                $contact->set('accountId', $account->getId());
                $contact->set('accountName', $account->get('name'));
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return;
        }

        $this->entityManager->saveEntity($contact, [SaveOption::SILENT => true]);
    }

    private function syncAccount(Entity $lead, Entity $account): void
    {
        $hasChanges = false;

        $hasChanges = $this->setIfEmpty($account, 'phoneNumber', $lead->get('phoneNumber')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'emailAddress', $lead->get('emailAddress')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'billingAddressStreet', $lead->get('addressStreet')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'billingAddressCity', $lead->get('addressCity')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'billingAddressState', $lead->get('addressState')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'billingAddressPostalCode', $lead->get('addressPostalCode')) || $hasChanges;
        $hasChanges = $this->setIfEmpty($account, 'billingAddressCountry', $lead->get('addressCountry')) || $hasChanges;

        if (!$hasChanges) {
            return;
        }

        $this->entityManager->saveEntity($account, [SaveOption::SILENT => true]);
    }

    private function setIfEmpty(Entity $entity, string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $currentValue = $entity->get($field);

        if ($currentValue !== null && $currentValue !== '') {
            return false;
        }

        $entity->set($field, $value);

        return true;
    }
}
