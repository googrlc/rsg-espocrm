<?php

namespace Espo\Custom\Hooks\Contact;

use DateTimeImmutable;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveProfile implements BeforeSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $activePolicyCount = 0;

        if ($entity->getId()) {
            $activePolicyCount = $this->entityManager
                ->getRDBRepository('Policy')
                ->where([
                    'contactId' => $entity->getId(),
                    'status' => 'Active',
                ])
                ->count();
        }

        $createdAt = $entity->get('createdAt') ?: $entity->getFetched('createdAt');
        $createdYear = $createdAt ? (new DateTimeImmutable($createdAt))->format('Y') : (new DateTimeImmutable())->format('Y');

        $derivedDescription = "Active Policies: {$activePolicyCount}\nClient Since: {$createdYear}";
        $existingDescription = trim((string) ($entity->get('description') ?? ''));
        $previousDerived = trim((string) ($entity->getFetched('description') ?? ''));

        if ($existingDescription === '' || $existingDescription === $previousDerived) {
            $entity->set('description', $derivedDescription);
        }

        $dateOfBirth = $entity->get('dateOfBirth');
        if ($dateOfBirth) {
            $birthday65 = (new DateTimeImmutable($dateOfBirth))->modify('+65 years');
            $today = new DateTimeImmutable('today');
            $daysUntil65 = (int) $today->diff($birthday65)->format('%r%a');
            $entity->set('daysUntil65', $daysUntil65);
        } else {
            $entity->set('daysUntil65', null);
        }

        if (!$entity->get('originalLeadSource')) {
            $campaignName = $entity->get('campaignName') ?: $entity->getFetched('campaignName');
            if ($campaignName) {
                $entity->set('originalLeadSource', $campaignName);
            }
        }
    }
}
