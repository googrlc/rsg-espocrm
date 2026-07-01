<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Two-tier disposition gate.
 *
 * Tier 1 — "won" dispositions (renewed, rewritten):
 *   Require that a linked RenewalWorksheet exists with completion_type = full_review.
 *
 * Tier 2 — "exit" dispositions (lost, dnr):
 *   Accept any completion_type (contact_only or full_review); worksheet is optional
 *   but if present must be completed.
 *
 * Once a disposition is set it is immutable (enforced here and in clientDefs readOnly logic).
 */
class GateDisposition implements BeforeSave
{
    private const WON_DISPOSITIONS  = ['renewed', 'rewritten'];
    private const EXIT_DISPOSITIONS = ['lost', 'dnr'];

    public function __construct(
        private \Espo\ORM\EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $newDisposition = (string) ($entity->get('disposition') ?? '');
        if ($newDisposition === '') {
            return;
        }

        $previousDisposition = (string) ($entity->getFetched('disposition') ?? '');

        if ($previousDisposition !== '' && $previousDisposition !== $newDisposition) {
            throw new Forbidden('Disposition is immutable once set.');
        }

        if ($previousDisposition === $newDisposition) {
            return;
        }

        if (in_array($newDisposition, self::WON_DISPOSITIONS, true)) {
            $this->assertFullReviewWorksheet($entity);
        } elseif (in_array($newDisposition, self::EXIT_DISPOSITIONS, true)) {
            $this->assertExitWorksheetIfPresent($entity);
        }
    }

    private function assertFullReviewWorksheet(Entity $renewal): void
    {
        if (!$renewal->hasId()) {
            throw new Forbidden(
                'Cannot set disposition to "renewed" or "rewritten" on an unsaved renewal.'
            );
        }

        $worksheet = $this->entityManager
            ->getRDBRepository('RenewalWorksheet')
            ->where([
                'renewalId'      => $renewal->getId(),
                'completion_type' => 'full_review',
                'state'          => 'completed',
            ])
            ->findOne();

        if (!$worksheet) {
            throw new Forbidden(
                'Disposition "' . $renewal->get('disposition') . '" requires a completed full-review worksheet. '
                . 'Please submit the worksheet with all required fields before setting disposition.'
            );
        }
    }

    private function assertExitWorksheetIfPresent(Entity $renewal): void
    {
        if (!$renewal->hasId()) {
            return;
        }

        $worksheet = $this->entityManager
            ->getRDBRepository('RenewalWorksheet')
            ->where(['renewalId' => $renewal->getId()])
            ->findOne();

        if (!$worksheet) {
            return;
        }

        $completionType = (string) ($worksheet->get('completion_type') ?? '');
        $state = (string) ($worksheet->get('state') ?? '');

        if ($state !== 'completed') {
            throw new Forbidden(
                'A worksheet is in progress. Complete or submit it before setting disposition.'
            );
        }

        if ($completionType === '') {
            throw new Forbidden(
                'Worksheet completion type is not set. Submit the worksheet first.'
            );
        }
    }
}
