<?php

namespace Espo\Custom\Hooks\Policy;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateCarrierUnderwriter implements BeforeSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $carrierAccountId = trim((string) ($entity->get('carrierAccountId') ?? ''));
        $underwriterId = trim((string) ($entity->get('underwriterId') ?? ''));

        if ($carrierAccountId === '') {
            if ($underwriterId !== '') {
                throw new BadRequest('Carrier Account is required when Underwriter is set.');
            }

            return;
        }

        if ($underwriterId === '') {
            return;
        }

        $underwriter = $this->entityManager->getEntityById('Contact', $underwriterId);
        if (!$underwriter) {
            throw new BadRequest('Selected underwriter could not be found.');
        }

        $contactRole = trim((string) ($underwriter->get('contactRole') ?? ''));
        if ($contactRole !== 'Underwriter') {
            throw new BadRequest('Selected contact must have Contact Role set to Underwriter.');
        }

        $isLinkedToCarrier = $this->entityManager
            ->getRDBRepository('AccountContact')
            ->where([
                'accountId' => $carrierAccountId,
                'contactId' => $underwriterId,
                'deleted' => false,
            ])
            ->count() > 0;

        if (!$isLinkedToCarrier) {
            throw new BadRequest('Selected underwriter must be linked to the chosen Carrier Account.');
        }
    }
}
