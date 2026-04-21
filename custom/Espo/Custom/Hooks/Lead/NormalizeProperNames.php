<?php

namespace Espo\Custom\Hooks\Lead;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Name\ProperNameNormalizer;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class NormalizeProperNames implements BeforeSave
{
    private const FIELDS = [
        'firstName',
        'lastName',
        'accountName',
    ];

    public function __construct(
        private ProperNameNormalizer $properNameNormalizer
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        foreach (self::FIELDS as $field) {
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
