<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOption;
use Espo\ORM\Repository\Option\SaveOptions;

class SeedDriverFromContact implements AfterSave
{
    private const AUTO_LINE_OF_BUSINESS = [
        'Commercial Auto',
        'Transportation / Trucking',
        'Personal Auto',
        'Motorcycle',
        'Boat',
        'RV',
        'Garagekeepers',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $opportunityId = (string) ($entity->getId() ?? '');
        if ($opportunityId === '') {
            return;
        }

        $lineOfBusiness = trim((string) ($entity->get('line_of_business') ?? ''));
        if (!in_array($lineOfBusiness, self::AUTO_LINE_OF_BUSINESS, true)) {
            return;
        }

        $existingDriver = $this->entityManager
            ->getRDBRepository('OpportunityDriver')
            ->where(['opportunityId' => $opportunityId])
            ->findOne();
        if ($existingDriver) {
            return;
        }

        $expectedDriverCount = $this->resolveExpectedDriverCount($entity);
        if ($expectedDriverCount > 1) {
            return;
        }

        $contact = $this->resolvePrimaryContact($entity);
        if (!$contact) {
            return;
        }

        $driverName = trim((string) ($contact->get('name') ?? ''));
        if ($driverName === '') {
            return;
        }

        $driver = $this->entityManager->getNewEntity('OpportunityDriver');
        $driver->set([
            'name' => $driverName,
            'driverName' => $driverName,
            'dateOfBirth' => $contact->get('dateOfBirth'),
            'stateIssued' => (string) ($contact->get('addressState') ?? ''),
            'opportunityId' => $opportunityId,
            'opportunityName' => (string) ($entity->get('name') ?? ''),
            'accountId' => $entity->get('accountId'),
            'accountName' => (string) ($entity->get('accountName') ?? ''),
            'lineOfBusiness' => $lineOfBusiness,
        ]);

        $this->entityManager->saveEntity($driver, [SaveOption::SILENT => true]);
    }

    private function resolveExpectedDriverCount(Entity $opportunity): int
    {
        $counts = [
            (int) ($opportunity->get('ca_driver_count') ?? 0),
            (int) ($opportunity->get('auto_driver_count') ?? 0),
        ];

        $max = max($counts);

        return $max > 0 ? $max : 1;
    }

    private function resolvePrimaryContact(Entity $opportunity): ?Entity
    {
        $contactId = trim((string) ($opportunity->get('contactId') ?? ''));
        if ($contactId === '') {
            $contactsIds = $opportunity->get('contactsIds') ?? [];
            if (is_array($contactsIds) && !empty($contactsIds)) {
                $contactId = trim((string) $contactsIds[0]);
            }
        }

        if ($contactId === '') {
            return null;
        }

        return $this->entityManager->getEntityById('Contact', $contactId);
    }
}
