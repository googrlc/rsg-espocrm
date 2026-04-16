<?php
namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class WonBoundValidation implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $stage = $entity->get('stage');

        if ($stage !== 'Closed Won' && $stage !== 'Bound / Renewed') {
            return;
        }

        $missing = [];

        if (!$entity->get('bindDate')) {
            $missing[] = 'Bind Date';
        }

        $writtenPremium = $entity->get('writtenPremium');
        if ($writtenPremium === null || $writtenPremium === '') {
            $missing[] = 'Written Premium';
        }

        if (!$entity->get('effectiveDate')) {
            $missing[] = 'Effective Date';
        }

        if (!empty($missing)) {
            throw new BadRequest(
                'The following fields are required when Stage is "Closed Won" or "Bound / Renewed": ' .
                implode(', ', $missing) .
                '.'
            );
        }
    }
}
