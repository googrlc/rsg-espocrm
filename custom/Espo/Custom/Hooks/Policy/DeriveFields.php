<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Custom\Classes\Policy\PolicyAccountSync;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class DeriveFields implements BeforeSave
{
    public function __construct(
        private PolicyAccountSync $policyAccountSync
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $this->policyAccountSync->applyDerivedFields($entity);
    }
}
