<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Policy\PolicySyncAuditLogger;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class EnforceAmsPolicyLock implements BeforeSave
{
    private const CORE_FIELDS = [
        'policyNumber',
        'status',
        'carrier',
        'lineOfBusiness',
        'effectiveDate',
        'expirationDate',
        'premiumAmount',
        'businessType',
        'bindDate',
        'billingType',
        'policyTerm',
        'cancellationDate',
        'reinstatementDate',
        'momentumPolicyId',
        'insuredMomentumId',
    ];

    public function __construct(
        private PolicySyncAuditLogger $policySyncAuditLogger
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $amsPolicyId = trim((string) ($entity->get('momentumPolicyId') ?? ''));
        if ($amsPolicyId === '') {
            return;
        }

        if ($this->hasFieldChanged($entity, 'policyNumber')) {
            $this->policySyncAuditLogger->logDecision(
                $entity,
                'rejected',
                'CRM attempted to change policyNumber on AMS-linked policy.',
                [
                    'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                    'changedFields' => ['policyNumber'],
                ]
            );

            throw new BadRequest(
                'Policy Number cannot be changed from CRM for AMS-linked policies. Update it in AMS.'
            );
        }

        $lockState = trim((string) ($entity->get('amsLockState') ?? ''));
        if ($lockState !== 'Locked by AMS') {
            return;
        }

        $changedCoreFields = $this->detectChangedCoreFields($entity);
        if ($changedCoreFields === []) {
            return;
        }

        $this->policySyncAuditLogger->logDecision(
            $entity,
            'blocked',
            'Attempted update to locked policy core fields in CRM.',
            [
                'sourceTimestamp' => (string) ($entity->get('modifiedAt') ?? ''),
                'changedFields' => $changedCoreFields,
            ]
        );

        throw new BadRequest(
            'Policy core fields are locked in CRM after AMS acceptance. Update these fields in AMS.'
        );
    }

    /**
     * @return array<int, string>
     */
    private function detectChangedCoreFields(Entity $entity): array
    {
        $changedFields = [];

        foreach (self::CORE_FIELDS as $field) {
            if ($this->normalizeValue($entity->get($field)) === $this->normalizeValue($entity->getFetched($field))) {
                continue;
            }

            $changedFields[] = $field;
        }

        return $changedFields;
    }

    private function normalizeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return json_encode($value) ?: '';
    }

    private function hasFieldChanged(Entity $entity, string $field): bool
    {
        return $this->normalizeValue($entity->get($field)) !== $this->normalizeValue($entity->getFetched($field));
    }
}
