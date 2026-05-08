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

        if (!$this->isConvertedStatus($status) || $this->isConvertedStatus($fetchedStatus)) {
            return;
        }

        $this->syncConvertedMetadata($entity);
        $this->createConversionAuditEntry($entity, $status);

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
        }
    }

    private function syncConvertedMetadata(Entity $lead): void
    {
        $hasChanges = false;

        if ((string) ($lead->get('convertedDate') ?? '') === '') {
            $lead->set('convertedDate', date('Y-m-d'));
            $hasChanges = true;
        }

        $convertedOpportunityId = (string) ($lead->get('convertedOpportunityId') ?? '');
        $createdOpportunityId = (string) ($lead->get('createdOpportunityId') ?? '');

        if ($convertedOpportunityId === '' && $createdOpportunityId !== '') {
            $opportunity = $this->entityManager->getEntityById('Opportunity', $createdOpportunityId);
            if ($opportunity) {
                $lead->set('convertedOpportunityId', $opportunity->getId());
                $lead->set('convertedOpportunityName', (string) ($opportunity->get('name') ?? ''));
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return;
        }

        $this->entityManager->saveEntity($lead, [SaveOption::SILENT => true]);
    }

    private function createConversionAuditEntry(Entity $lead, string $status): void
    {
        $leadId = (string) $lead->getId();
        if ($leadId === '') {
            return;
        }

        $opportunityId = trim((string) ($lead->get('createdOpportunityId') ?? ''));
        if ($opportunityId === '') {
            $opportunityId = trim((string) ($lead->get('convertedOpportunityId') ?? ''));
        }

        $accountId = trim((string) ($lead->get('createdAccountId') ?? ''));
        $contactId = trim((string) ($lead->get('createdContactId') ?? ''));
        $convertedByUserId = trim((string) ($lead->get('modifiedById') ?? ''));
        $convertedByUserName = trim((string) ($lead->get('modifiedByName') ?? ''));

        $convertedAt = date('Y-m-d H:i:s');
        $leadName = trim((string) ($lead->get('name') ?? ''));
        $audit = $this->entityManager->getNewEntity('LeadConversionAudit');

        $audit->set([
            'name' => sprintf(
                'Lead Conversion Audit - %s - %s',
                $leadName !== '' ? $leadName : $leadId,
                $convertedAt
            ),
            'leadRecordId' => $leadId,
            'opportunityRecordId' => $opportunityId !== '' ? $opportunityId : null,
            'accountRecordId' => $accountId !== '' ? $accountId : null,
            'contactRecordId' => $contactId !== '' ? $contactId : null,
            'convertedAt' => $convertedAt,
            'leadStatusAtConversion' => $status,
            'leadId' => $leadId,
            'leadName' => $leadName !== '' ? $leadName : null,
            'opportunityId' => $opportunityId !== '' ? $opportunityId : null,
            'accountId' => $accountId !== '' ? $accountId : null,
            'contactId' => $contactId !== '' ? $contactId : null,
            'convertedByUserId' => $convertedByUserId !== '' ? $convertedByUserId : null,
            'convertedByUserName' => $convertedByUserName !== '' ? $convertedByUserName : 'System',
            'assignedUserId' => $convertedByUserId !== '' ? $convertedByUserId : null,
            'assignedUserName' => $convertedByUserName !== '' ? $convertedByUserName : null,
            'teamsIds' => $lead->get('teamsIds') ?? [],
        ]);

        $this->entityManager->saveEntity($audit, [SaveOption::SILENT => true]);
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

    private function isConvertedStatus(string $status): bool
    {
        return in_array($status, ['Converted', 'Converted to Opportunity'], true);
    }
}
