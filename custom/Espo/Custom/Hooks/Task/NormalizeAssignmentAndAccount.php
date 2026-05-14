<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class NormalizeAssignmentAndAccount implements BeforeSave
{
    private const ALLOWED_USERS = [
        '69bdf81552aaa' => 'Gretchen Coates',
        '69bdad92458da2204' => 'Lamar Coates',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $this->normalizeAssignedUser($entity);
        $this->normalizeAccount($entity);
    }

    private function normalizeAssignedUser(Entity $entity): void
    {
        $assignedUserId = trim((string) ($entity->get('assignedUserId') ?? ''));

        if ($assignedUserId === '') {
            return;
        }

        if (!array_key_exists($assignedUserId, self::ALLOWED_USERS)) {
            throw new BadRequest('Assigned To must be Gretchen Coates or Lamar Coates.');
        }

        $user = $this->entityManager->getEntityById('User', $assignedUserId);
        $entity->set('assignedUserName', $user ? $user->get('name') : self::ALLOWED_USERS[$assignedUserId]);
    }

    private function normalizeAccount(Entity $entity): void
    {
        $accountId = trim((string) ($entity->get('accountId') ?? ''));
        if ($accountId === '') {
            $accountId = trim((string) ($entity->get('linkedAccountId') ?? ''));
        }

        if ($accountId !== '') {
            $account = $this->entityManager->getEntityById('Account', $accountId);

            if (!$account) {
                throw new BadRequest('Selected account could not be found.');
            }

            $entity->set('accountId', $account->getId());
            $entity->set('accountName', $account->get('name'));
            $entity->set('linkedAccountId', $account->getId());
            $entity->set('linkedAccountName', $account->get('name'));
        }
    }
}
