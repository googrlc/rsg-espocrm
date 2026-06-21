<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class NormalizeAssignmentAndAccount implements BeforeSave
{
    /**
     * Users allowed to own a task. Accept display names as the durable business
     * rule, with usernames as aliases for older saved code paths.
     */
    private const ALLOWED_USER_NAMES = ['Gretchen Coates', 'Lamar Coates'];
    private const ALLOWED_USERNAME_ALIASES = [
        'gretchcoates',
        'lamarcoates',
        'lamar@risk-solutionsgroup.com',
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

        $user = $this->entityManager->getEntityById('User', $assignedUserId);
        $userName = $user ? (string) $user->get('userName') : '';
        $name = $user ? (string) $user->get('name') : '';

        if (!$this->isAllowedOwner($name, $userName)) {
            throw new BadRequest('Assigned To must be Gretchen Coates or Lamar Coates.');
        }

        // $user is guaranteed non-null here (its name or username matched the allow list).
        $entity->set('assignedUserName', $user->get('name'));
    }

    private function isAllowedOwner(string $name, string $userName): bool
    {
        return
            in_array($name, self::ALLOWED_USER_NAMES, true) ||
            in_array($userName, self::ALLOWED_USERNAME_ALIASES, true);
    }

    private function normalizeAccount(Entity $entity): void
    {
        $accountId = trim((string) ($entity->get('accountId') ?? ''));

        if ($accountId === '') {
            $accountId = trim((string) ($entity->get('linkedAccountId') ?? ''));
        }

        // Fall back to the parent record when it is an Account. Tasks created
        // from the Account's "Tasks" panel only set the parent link, so without
        // this the Account/"Insured" link (used by rollups and Kanban) stays
        // empty and the task does not fully link back to the Account.
        $derivedFromParent = false;

        if ($accountId === '' && $entity->get('parentType') === 'Account') {
            $accountId = trim((string) ($entity->get('parentId') ?? ''));
            $derivedFromParent = $accountId !== '';
        }

        if ($accountId === '') {
            return;
        }

        $account = $this->entityManager->getEntityById('Account', $accountId);

        if (!$account) {
            throw new BadRequest('Selected account could not be found.');
        }

        $entity->set('accountId', $account->getId());
        $entity->set('accountName', $account->get('name'));
        $entity->set('linkedAccountId', $account->getId());
        $entity->set('linkedAccountName', $account->get('name'));

        // Attribute the task to the Account rollup when it was created straight
        // from the Account record (parent-only create path).
        if ($derivedFromParent && trim((string) ($entity->get('taskSource') ?? '')) === '') {
            $entity->set('taskSource', 'Account');
        }
    }
}
