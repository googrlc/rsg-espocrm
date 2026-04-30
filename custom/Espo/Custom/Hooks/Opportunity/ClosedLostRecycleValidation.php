<?php

namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class ClosedLostRecycleValidation implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $stage = (string) ($entity->get('stage') ?? '');
        $recycleToLead = (bool) ($entity->get('recycle_to_lead') ?? false);

        if ($stage !== 'Closed Lost' || !$recycleToLead) {
            return;
        }

        $xDate = trim((string) ($entity->get('x_date') ?? ''));
        if ($xDate === '') {
            throw new BadRequest(
                'Renewal X-Date is required when recycling a Closed Lost opportunity to Lead nurture.'
            );
        }
    }
}
