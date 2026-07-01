<?php

namespace Espo\Custom\Hooks\RenewalWorksheet;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Renewal Loop v6 §3 — auto-progress state and lock lob_variant.
 *
 *  - not_started -> in_progress on the first edit that doesn't itself advance
 *    the state (SubmitWorksheet sets state=completed and bypasses this).
 *  - lob_variant is locked once the worksheet has left not_started: a later
 *    attempt to change it is rejected (the per-variant attestation set would
 *    no longer match what was reviewed).
 */
class AutoProgressAndLock implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        // Skip on creation: a freshly-created worksheet stays not_started until
        // the first real edit (or SubmitWorksheet advances it to completed).
        if ($entity->isNew()) {
            return;
        }

        $fetchedState = trim((string) ($entity->getFetched('state') ?? ''));
        $newState = trim((string) ($entity->get('state') ?? ''));
        $notStarted = ($fetchedState === '' || $fetchedState === 'not_started');

        // Auto-progress: still not_started and the save isn't advancing state itself.
        if ($notStarted && ($newState === '' || $newState === 'not_started')) {
            $entity->set('state', 'in_progress');
        }

        // Lock lob_variant once past not_started.
        $fetchedVariant = (string) ($entity->getFetched('lob_variant') ?? '');
        $newVariant = (string) ($entity->get('lob_variant') ?? '');
        if (!$notStarted && $fetchedVariant !== '' && $newVariant !== $fetchedVariant) {
            throw new \RuntimeException(
                "lob_variant is locked once the worksheet is in progress "
                . "(was '{$fetchedVariant}', attempted '{$newVariant}')."
            );
        }
    }
}
