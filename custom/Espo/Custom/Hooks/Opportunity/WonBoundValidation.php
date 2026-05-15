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
        $stage = $entity->get("stage");

        if ($stage !== "Closed Won") {
            return;
        }

        $missing = [];

        if (!$entity->get("bind_date")) {
            $missing[] = "Bind Date";
        }

        if (!$entity->get("written_premium")) {
            $missing[] = "Written Premium";
        }

        if (!$entity->get("effective_date")) {
            $missing[] = "Effective Date";
        }

        if (!empty($missing)) {
            throw new BadRequest(
                "The following fields are required when Stage is \"Closed Won\": " .
                implode(", ", $missing) . "."
            );
        }
    }
}
