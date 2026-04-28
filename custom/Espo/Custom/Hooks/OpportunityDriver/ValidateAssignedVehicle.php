<?php

namespace Espo\Custom\Hooks\OpportunityDriver;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateAssignedVehicle implements BeforeSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $opportunityId = trim((string) ($entity->get('opportunityId') ?? ''));
        if ($opportunityId === '') {
            return;
        }

        $vehicleCount = $this->entityManager
            ->getRDBRepository('OpportunityVehicle')
            ->where(['opportunityId' => $opportunityId])
            ->count();

        if ($vehicleCount <= 1) {
            return;
        }

        $vehicleId = trim((string) ($entity->get('vehicleId') ?? ''));
        if ($vehicleId !== '') {
            return;
        }

        throw new BadRequest(
            'Assigned Vehicle is required for each driver when more than one vehicle exists on the opportunity.'
        );
    }
}
