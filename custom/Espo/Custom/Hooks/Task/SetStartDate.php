<?php

namespace Espo\Custom\Hooks\Task;

use DateTimeImmutable;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * Auto-stamps Task.dateStart the moment work begins.
 *
 *  - status -> "In Progress" (incl. a new task created already In Progress,
 *    e.g. every COI request) with empty dateStart  => set dateStart = now
 *
 * Start Date is never entered by hand (the field is readOnly in entityDefs and
 * removed from every form); this hook owns it. It records the FIRST time the
 * task entered "In Progress": once set it is left alone, so re-entering
 * In Progress after a "Waiting on …" detour does not move it. Idempotent.
 *
 * Applies to ALL task types — Task is the only entity with an "In Progress"
 * status, so this satisfies the system-wide "start date on In Progress" rule.
 *
 * dateStart is a datetimeOptional field: we always write a full datetime
 * (never a date-only string, which would 500 with "Bad value").
 *
 * Auto-registers by path: Hooks/Task/SetStartDate.php.
 */
class SetStartDate implements BeforeSave
{
    private const IN_PROGRESS = 'In Progress';

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged('status')) {
            return;
        }

        $status = (string) ($entity->get('status') ?? '');
        $previous = $entity->isNew() ? null : $entity->getFetched('status');

        if ($status === self::IN_PROGRESS && $previous !== self::IN_PROGRESS) {
            if (!$entity->get('dateStart')) {
                $entity->set('dateStart', (new DateTimeImmutable())->format('Y-m-d H:i:s'));
            }
        }
    }
}
