<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateLifeBeneficiaries implements BeforeSave
{
    private const ENFORCEMENT_STAGES = ['Negotiation', 'Closed Won'];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->get('lineOfBusiness') !== 'Life') {
            return;
        }

        if (!$this->hasAnyRowInUse($entity)) {
            return;
        }

        if (!$this->shouldEnforceCompletenessAndSum($entity)) {
            return;
        }

        $messages = [];

        for ($i = 1; $i <= 3; $i++) {
            foreach ($this->collectRowCompletenessMessages($entity, $i) as $msg) {
                $messages[] = $msg;
            }
        }

        $sum = $this->sumPercentagesForRowsInUse($entity);
        if ($sum !== 100) {
            $messages[] = 'Beneficiary percentages must equal 100%.';
        }

        if ($messages !== []) {
            throw new BadRequest(implode("\n", $messages));
        }
    }

    private function shouldEnforceCompletenessAndSum(Entity $entity): bool
    {
        $stage = $this->resolveStage($entity);

        return in_array($stage, self::ENFORCEMENT_STAGES, true);
    }

    private function resolveStage(Entity $entity): string
    {
        $stage = trim((string) ($entity->get('stage') ?? ''));
        if ($stage !== '') {
            return $stage;
        }

        if ($entity->isNew()) {
            return '';
        }

        $id = $entity->getId();
        if (!$id) {
            return '';
        }

        $existing = $this->entityManager->getEntityById('Opportunity', $id);
        if (!$existing) {
            return '';
        }

        return trim((string) ($existing->get('stage') ?? ''));
    }

    private function hasAnyRowInUse(Entity $entity): bool
    {
        for ($i = 1; $i <= 3; $i++) {
            if ($this->rowIsInUse($entity, $i)) {
                return true;
            }
        }

        return false;
    }

    private function rowIsInUse(Entity $entity, int $index): bool
    {
        $name = trim((string) ($entity->get('beneficiary' . $index . 'Name') ?? ''));

        return $name !== '' || $this->getPctInt($entity, $index) > 0;
    }

    /**
     * @return string[]
     */
    private function collectRowCompletenessMessages(Entity $entity, int $index): array
    {
        if (!$this->rowIsInUse($entity, $index)) {
            return [];
        }

        $label = 'Beneficiary ' . $index;
        $hasName = trim((string) ($entity->get('beneficiary' . $index . 'Name') ?? '')) !== '';
        $hasRel = $this->relationshipIsSet($entity, $index);
        $pct = $this->getPctInt($entity, $index);
        $hasPctPositive = $pct > 0;

        $messages = [];

        if ($hasPctPositive && !$hasName) {
            $messages[] = $label . ': Name is required when share % is set.';
        }

        if (($hasName || $hasPctPositive) && !$hasRel) {
            $messages[] = $label . ': Relationship is required when name or share % is set.';
        }

        if (($hasName || $hasRel) && !$hasPctPositive) {
            $messages[] = $label . ': Share % is required when name or relationship is set.';
        }

        return $messages;
    }

    private function relationshipIsSet(Entity $entity, int $index): bool
    {
        $rel = $entity->get('beneficiary' . $index . 'Relationship');

        return $rel !== null && $rel !== '';
    }

    private function getPctInt(Entity $entity, int $index): int
    {
        $pct = $entity->get('beneficiary' . $index . 'Pct');
        if ($pct === null || $pct === '') {
            return 0;
        }

        return (int) $pct;
    }

    private function sumPercentagesForRowsInUse(Entity $entity): int
    {
        $sum = 0;
        for ($i = 1; $i <= 3; $i++) {
            if (!$this->rowIsInUse($entity, $i)) {
                continue;
            }
            $sum += $this->getPctInt($entity, $i);
        }

        return $sum;
    }
}
