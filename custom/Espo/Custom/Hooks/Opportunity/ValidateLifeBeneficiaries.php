<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateLifeBeneficiaries implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->get('lineOfBusiness') !== 'Life') {
            return;
        }

        $hasAny = false;
        for ($i = 1; $i <= 3; $i++) {
            if ($this->rowHasAnyField($entity, $i)) {
                $hasAny = true;
                break;
            }
        }

        if (!$hasAny) {
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            if (!$this->rowHasAnyField($entity, $i)) {
                continue;
            }

            if (!$this->rowIsComplete($entity, $i)) {
                throw new BadRequest(
                    'Each beneficiary row must include name, relationship, and percentage.'
                );
            }
        }

        $sum = 0;
        for ($i = 1; $i <= 3; $i++) {
            $sum += (int) ($entity->get('beneficiary' . $i . 'Pct') ?? 0);
        }

        if ($sum !== 100) {
            throw new BadRequest('Beneficiary percentages must equal 100%.');
        }
    }

    private function rowHasAnyField(Entity $entity, int $index): bool
    {
        $name = trim((string) ($entity->get('beneficiary' . $index . 'Name') ?? ''));
        $rel = $entity->get('beneficiary' . $index . 'Relationship');
        $pct = $entity->get('beneficiary' . $index . 'Pct');

        $hasRel = $rel !== null && $rel !== '';
        $hasPct = $pct !== null && $pct !== '';

        return $name !== '' || $hasRel || $hasPct;
    }

    private function rowIsComplete(Entity $entity, int $index): bool
    {
        $name = trim((string) ($entity->get('beneficiary' . $index . 'Name') ?? ''));
        $rel = $entity->get('beneficiary' . $index . 'Relationship');
        $pct = $entity->get('beneficiary' . $index . 'Pct');

        $hasRel = $rel !== null && $rel !== '';
        $hasPct = $pct !== null && $pct !== '';

        return $name !== '' && $hasRel && $hasPct;
    }
}
