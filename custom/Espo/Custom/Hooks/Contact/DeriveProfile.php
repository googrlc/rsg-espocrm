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

        if ($existingDescription === '' || $this->looksLikeDerivedDescription($existingDescription)) {
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

    /**
     * Detect whether the existing description matches the auto-derived shape
     * (`Active Policies: N\nClient Since: YYYY`). User-typed notes won't match,
     * so they are preserved across saves.
     */
    private function looksLikeDerivedDescription(string $value): bool
    {
        return (bool) preg_match(
            '/\AActive Policies:\s*\d+\s*[\r\n]+Client Since:\s*\d{4}\s*\z/',
            $value
        );
    }
}
