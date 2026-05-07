<?php

namespace Espo\Custom\Hooks\Contact;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Name\ProperNameNormalizer;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class NormalizeProperNames implements BeforeSave
{
    private const FIELDS = [
        'firstName',
        'lastName',
        'middleName',
        'csrName',
    ];

    public function __construct(
        private ProperNameNormalizer $properNameNormalizer
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $processed = false;
        $reviewNeeded = false;

        foreach (self::FIELDS as $field) {
            $value = $entity->get($field);
            if ($value === null || $value === '') {
                continue;
            }
            if (!is_string($value)) {
                continue;
            }
            $processed = true;

            $result = $this->properNameNormalizer->normalizeWithReview($value);
            if ($result['reviewNeeded']) {
                $reviewNeeded = true;
                continue;
            }

            if ($result['normalized'] !== null) {
                $entity->set($field, $result['normalized']);
            }
        }

        if ($processed) {
            $entity->set('properNounReviewNeeded', $reviewNeeded);
        }
    }
}
