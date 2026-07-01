<?php

namespace Espo\Custom\Services;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Modules\RsgCore\Config\RenewalWorksheetRequirements;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * Renewal Loop v6 §4 — SubmitWorksheet service.
 *
 * Derives ``completion_type`` from the attestation state of the worksheet's
 * variant fields and advances state to ``completed``:
 *   - zero variant fields set  -> contact_only
 *   - ALL required fields set  -> full_review
 *   - a partial set            -> rejected with the missing required fields
 *
 * Booleans count as "set" only when true; enum attestations when non-empty and
 * non-na; numeric auxiliary fields when non-null. ``client_contacted`` (or a
 * ``no_contact_reason``) is required for any submission.
 */
class RenewalWorksheetSubmit
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    /** @return array{id:string,completion_type:string,state:string} */
    public function submit(string $id): array
    {
        $worksheet = $this->entityManager->getEntityById('RenewalWorksheet', $id);
        if ($worksheet === null) {
            throw new NotFound("RenewalWorksheet {$id} not found.");
        }

        // client_contacted is required for any disposition (won or lost).
        $contacted = (bool) ($worksheet->get('client_contacted') ?? false);
        $noContactReason = trim((string) ($worksheet->get('no_contact_reason') ?? ''));
        if (!$contacted && $noContactReason === '') {
            throw new BadRequest(
                'Mark "Client Contacted", or choose a "No Contact Reason" before submitting the worksheet.'
            );
        }

        $variant = trim((string) ($worksheet->get('lob_variant') ?? '')) ?: 'default';
        $variantFields = RenewalWorksheetRequirements::fieldsForVariant($variant);
        $required = RenewalWorksheetRequirements::requiredForVariant($variant);

        $setCount = 0;
        foreach ($variantFields as $field) {
            if ($this->isSet($worksheet, $field)) {
                $setCount++;
            }
        }

        $missing = [];
        foreach ($required as $field) {
            if (!$this->isSet($worksheet, $field)) {
                $missing[] = $field;
            }
        }

        if ($setCount === 0) {
            $completionType = 'contact_only';
        } elseif ($missing === []) {
            $completionType = 'full_review';
        } else {
            throw new BadRequest(
                "Worksheet is partially complete — either clear the attestation fields for a "
                . "contact_only submission, or complete the required fields for {$variant}: "
                . implode(', ', $missing)
            );
        }

        $worksheet->set('completion_type', $completionType);
        $worksheet->set('state', 'completed');
        $this->entityManager->saveEntity($worksheet, [SaveOption::SILENT => true]);

        return [
            'id' => $id,
            'completion_type' => $completionType,
            'state' => 'completed',
        ];
    }

    private function isSet(Entity $worksheet, string $field): bool
    {
        $value = $worksheet->get($field);

        if (is_bool($value)) {
            return $value === true;
        }

        if (in_array($field, RenewalWorksheetRequirements::ENUM_FIELDS, true)) {
            $s = trim((string) $value);
            return $s !== '' && $s !== RenewalWorksheetRequirements::NA_VALUE;
        }

        // int / float auxiliary fields: non-null counts as set.
        return $value !== null && $value !== '';
    }
}
