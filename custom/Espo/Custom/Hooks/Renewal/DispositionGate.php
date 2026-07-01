<?php

namespace Espo\Custom\Hooks\Renewal;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Renewal Loop v6 §3 — two-tier disposition gate.
 *
 * A terminal disposition may only be set when the linked RenewalWorksheet has
 * been submitted with a compatible completion_type:
 *   - WIN (renewed / rewritten): requires completion_type = full_review
 *   - LOSS (lost_* / do_not_renew): accepts full_review OR contact_only
 * In-flight saves (no disposition) are not gated.
 */
class DispositionGate implements BeforeSave
{
    public const WIN_DISPOSITIONS = ['renewed', 'rewritten'];
    public const LOSS_DISPOSITIONS = ['lost_price', 'lost_coverage', 'lost_no_response', 'do_not_renew'];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $disposition = trim((string) ($entity->get('disposition') ?? ''));
        if ($disposition === '') {
            return; // in-flight — not gated
        }

        $worksheetId = (string) ($entity->get('worksheetId') ?? '');
        if ($worksheetId === '') {
            throw new \RuntimeException(
                'Cannot set a disposition until the RenewalWorksheet is created and submitted.'
            );
        }

        $worksheet = $this->entityManager->getEntityById('RenewalWorksheet', $worksheetId);
        if ($worksheet === null) {
            throw new \RuntimeException("Linked RenewalWorksheet {$worksheetId} not found.");
        }

        $completionType = trim((string) ($worksheet->get('completion_type') ?? ''));

        if (in_array($disposition, self::WIN_DISPOSITIONS, true)) {
            if ($completionType !== 'full_review') {
                throw new \RuntimeException(
                    "Disposition '{$disposition}' requires a full_review worksheet submission "
                    . "(current completion_type is '{$completionType}'). Re-open the worksheet, "
                    . 'complete the required attestations, and re-submit before marking this renewal won.'
                );
            }
            return;
        }

        if (in_array($disposition, self::LOSS_DISPOSITIONS, true)) {
            if (!in_array($completionType, ['full_review', 'contact_only'], true)) {
                throw new \RuntimeException(
                    "Disposition '{$disposition}' requires the worksheet to be submitted "
                    . "(completion_type is '{$completionType}'). Submit the worksheet — even a "
                    . 'contact_only submission is acceptable for a loss — before setting the disposition.'
                );
            }
            return;
        }
    }
}
