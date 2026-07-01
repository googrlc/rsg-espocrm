<?php

namespace Espo\Custom\Hooks\RenewalWorksheet;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Hook\Hook\BeforeSave as BeforeSaveHook;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * RenewalWorksheet BeforeSave:
 *
 * 1. Auto-progress state: not_started → in_progress on first field edit.
 * 2. Lock lob_variant once state is no longer not_started.
 */
class BeforeSave implements BeforeSaveHook
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $this->autoProgress($entity);
        $this->lockLobVariant($entity);
    }

    private function autoProgress(Entity $entity): void
    {
        $currentState = (string) ($entity->get('state') ?? 'not_started');
        $fetchedState = (string) ($entity->getFetched('state') ?? '');

        if ($currentState !== 'not_started') {
            return;
        }

        if ($fetchedState === '' || $fetchedState === 'not_started') {
            $worksheetFields = [
                'declaration_reviewed',
                'account_details_confirmed',
                'renewal_email_sent',
                'ams_updated',
                'vehicles_reviewed',
                'drivers_reviewed',
                'coverages_reviewed',
                'payroll_updated',
                'loss_runs_obtained',
                'property_schedule_reviewed',
                'contact_confirmed',
                'notes',
                'lob_variant',
            ];

            foreach ($worksheetFields as $field) {
                $newValue = $entity->get($field);
                $oldValue = $entity->getFetched($field);

                if ($newValue !== $oldValue && $newValue !== null && $newValue !== false && $newValue !== '') {
                    $entity->set('state', 'in_progress');
                    break;
                }
            }
        }
    }

    private function lockLobVariant(Entity $entity): void
    {
        $fetchedState = (string) ($entity->getFetched('state') ?? '');

        if ($fetchedState === '' || $fetchedState === 'not_started') {
            return;
        }

        $fetchedLobVariant = (string) ($entity->getFetched('lob_variant') ?? '');
        $newLobVariant = (string) ($entity->get('lob_variant') ?? '');

        if ($fetchedLobVariant !== '' && $newLobVariant !== $fetchedLobVariant) {
            throw new Forbidden(
                'The LOB variant cannot be changed once the worksheet has been started. '
                . 'Current variant: ' . $fetchedLobVariant
            );
        }
    }
}
