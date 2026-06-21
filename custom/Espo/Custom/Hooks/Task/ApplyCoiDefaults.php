<?php

namespace Espo\Custom\Hooks\Task;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * COI Request convenience defaults (server-side safety net).
 *
 * Whenever a task's type is "COI Requested", fill in the standard COI
 * starting point so Gretchen barely has to type — no matter how the task
 * was created (the "Request COI" button, the Tasks panel, or a plain Task
 * create where she just picks the type):
 *
 *   - new tasks: assign to Gretchen, set Status=In Progress, Priority=High
 *   - drop the client's NowCerts certificate deep-link into Ref Link (blank only)
 *   - insert the short COI description template (blank only)
 *   - default the name (blank only)
 *
 * Self-sufficient (resolves the Account on its own), so it is independent of
 * the order in which Task before-save hooks run.
 */
class ApplyCoiDefaults implements BeforeSave
{
    private const COI_TYPE = 'COI Requested';

    /** A COI request covers between 1 and this many policies. */
    private const MAX_POLICIES = 5;

    /** Resolved at runtime so a changed user id or login username can't break it. */
    private const GRETCHEN_NAME = 'Gretchen Coates';
    private const GRETCHEN_USERNAME_ALIASES = ['gretchcoates'];

    /**
     * NowCerts insured / certificate deep-link template.
     * {id} is replaced with the Account's NowCerts insured database_id
     * (Account.momentum_client_id).
     *
     * {id} = Account.momentum_client_id (NowCerts insured database_id, a GUID).
     * Lands on the insured's certificate/forms page where COIs are issued.
     */
    private const NOWCERTS_INSURED_URL = 'https://www6.nowcerts.com/AMSINS/Insureds/Details/{id}/PdfForms';

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (trim((string) ($entity->get('taskType') ?? '')) !== self::COI_TYPE) {
            return;
        }

        $this->validatePolicyCount($entity);

        if ($entity->isNew()) {
            $this->applyNewTaskDefaults($entity);
        }

        $account = $this->resolveAccount($entity);
        $accountName = $account ? trim((string) $account->get('name')) : '';

        $this->applyRefLink($entity, $account);

        if (trim((string) ($entity->get('description') ?? '')) === '') {
            $entity->set('description', $this->buildTemplate($accountName));
        }

        if (trim((string) ($entity->get('name') ?? '')) === '') {
            $entity->set('name', 'COI Request — ' . ($accountName !== '' ? $accountName : '[Client]'));
        }
    }

    /**
     * Enforce the COI 1–5 policy rule. The `policies` ids are only present on
     * the entity when that field is part of the save, so on an unrelated edit
     * of an existing task (ids not loaded) we skip the lower bound and never
     * block. New COI tasks must carry at least one policy; the upper bound is
     * always enforced as a safety net behind the client-side cap.
     */
    private function validatePolicyCount(Entity $entity): void
    {
        $ids = $entity->get('policiesIds');
        $count = is_array($ids) ? count($ids) : 0;

        if ($count > self::MAX_POLICIES) {
            throw new BadRequest('A COI request can reference at most ' . self::MAX_POLICIES . ' policies.');
        }

        if ($entity->isNew() && $count < 1) {
            throw new BadRequest('Select at least one policy for the COI request.');
        }
    }

    private function applyNewTaskDefaults(Entity $entity): void
    {
        if (trim((string) ($entity->get('assignedUserId') ?? '')) === '') {
            $gretchen = $this->resolveGretchen();

            if ($gretchen) {
                $entity->set('assignedUserId', $gretchen->getId());
                $entity->set('assignedUserName', $gretchen->get('name'));
            }
        }

        $status = trim((string) ($entity->get('status') ?? ''));
        if ($status === '' || $status === 'Inbox') {
            $entity->set('status', 'In Progress');
        }

        $priority = trim((string) ($entity->get('priority') ?? ''));
        if ($priority === '' || $priority === 'Normal') {
            $entity->set('priority', 'High');
        }
    }

    private function applyRefLink(Entity $entity, ?Entity $account): void
    {
        if (!$account) {
            return;
        }

        if (trim((string) ($entity->get('refLink') ?? '')) !== '') {
            return;
        }

        $nowCertsId = trim((string) ($account->get('momentum_client_id') ?? ''));

        if ($nowCertsId === '') {
            return;
        }

        $entity->set('refLink', str_replace('{id}', rawurlencode($nowCertsId), self::NOWCERTS_INSURED_URL));
    }

    private function resolveGretchen(): ?Entity
    {
        foreach (self::GRETCHEN_USERNAME_ALIASES as $userName) {
            $user = $this->entityManager
                ->getRDBRepository('User')
                ->where(['userName' => $userName])
                ->findOne();

            if ($user) {
                return $user;
            }
        }

        return $this->entityManager
            ->getRDBRepository('User')
            ->where(['name' => self::GRETCHEN_NAME])
            ->findOne();
    }

    private function resolveAccount(Entity $entity): ?Entity
    {
        $accountId = trim((string) ($entity->get('accountId') ?? ''));

        if ($accountId === '' && $entity->get('parentType') === 'Account') {
            $accountId = trim((string) ($entity->get('parentId') ?? ''));
        }

        if ($accountId === '') {
            return null;
        }

        return $this->entityManager->getEntityById('Account', $accountId);
    }

    private function buildTemplate(string $accountName): string
    {
        $client = $accountName !== '' ? $accountName : '[Client / Insured name]';

        return implode("\n", [
            'COI REQUEST — ' . $client,
            '',
            'Issue this certificate in NowCerts (open the client via the Ref Link above).',
            '',
            'Certificate Holder:',
            '[Insert certificate holder name and address]',
            '',
            'Additional Insured (if applicable):',
            '[Insert name if required]',
            '',
            'Special Instructions / Wording Required:',
            '[Insert any special language or requirements]',
            '',
            'Requested By: Gretchen',
        ]);
    }
}
