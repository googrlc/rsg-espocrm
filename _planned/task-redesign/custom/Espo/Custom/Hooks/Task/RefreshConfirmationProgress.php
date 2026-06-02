<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Maintains the read-only confirmationProgress field (e.g. "1/2") from the fixed
 * confirmation checklist fields. Runs in beforeSave so the value persists in the
 * same write (no extra save, no hook loop). Per-item status changes are logged
 * via the audited confirm{N}Status fields (stream/audit log).
 */
class RefreshConfirmationProgress implements BeforeSave
{
    private const SLOTS = 4;

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $total = 0;
        $confirmed = 0;

        for ($i = 1; $i <= self::SLOTS; $i++) {
            $label = trim((string) ($entity->get("confirm{$i}Label") ?? ''));

            if ($label === '') {
                continue;
            }

            $total++;

            if ((string) ($entity->get("confirm{$i}Status") ?? '') === 'Confirmed') {
                $confirmed++;
            }
        }

        $entity->set('confirmationProgress', $total > 0 ? "{$confirmed}/{$total}" : '');
    }
}
