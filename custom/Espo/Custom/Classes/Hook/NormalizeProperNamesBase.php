<?php

namespace Espo\Custom\Classes\Hook;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Name\ProperNameNormalizer;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

abstract class NormalizeProperNamesBase implements BeforeSave
{
    public function __construct(
        private ProperNameNormalizer $properNameNormalizer
    ) {}

    /** @return string[] */
    abstract protected function getFields(): array;

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        foreach ($this->getFields() as $field) {
            $value = $entity->get($field);
            if ($value === null || $value === '') {
                continue;
            }
            if (!is_string($value)) {
                continue;
            }
            $normalized = $this->properNameNormalizer->normalize($value);
            if ($normalized !== null) {
                $entity->set($field, $normalized);
            }
        }
    }
}
