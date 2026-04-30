<?php

namespace Espo\Custom\Hooks\OpportunityVehicle;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateVinForCommercial implements BeforeSave
{
    private const COMMERCIAL_LOB = [
        'Commercial Auto',
        'Transportation / Trucking',
        'Garagekeepers',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $lineOfBusiness = trim((string) ($entity->get('lineOfBusiness') ?? ''));
        if ($lineOfBusiness === '') {
            $opportunityId = trim((string) ($entity->get('opportunityId') ?? ''));
            if ($opportunityId !== '') {
                $opportunity = $this->entityManager->getEntityById('Opportunity', $opportunityId);
                if ($opportunity) {
                    $lineOfBusiness = trim((string) ($opportunity->get('line_of_business') ?? ''));
                }
            }
        }

        if (!in_array($lineOfBusiness, self::COMMERCIAL_LOB, true)) {
            return;
        }

        $vin = trim((string) ($entity->get('vin') ?? ''));
        if ($vin !== '') {
            return;
        }

        throw new BadRequest(
            'VIN is required for commercial auto-related lines of business (Commercial Auto, Transportation / Trucking, Garagekeepers).'
        );
    }
}
