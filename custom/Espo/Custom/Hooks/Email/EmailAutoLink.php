<?php
namespace Espo\Custom\Hooks\Email;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class EmailAutoLink implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->get('accountId')) {
            return;
        }

        $addresses = [];

        if ($entity->get('from')) {
            $addresses[] = strtolower($entity->get('from'));
        }

        $toList = $entity->get('to');
        if (is_string($toList)) {
            foreach (explode(';', $toList) as $addr) {
                $addr = trim($addr);
                if ($addr) $addresses[] = strtolower($addr);
            }
        }

        $ccList = $entity->get('cc');
        if (is_string($ccList)) {
            foreach (explode(';', $ccList) as $addr) {
                $addr = trim($addr);
                if ($addr) $addresses[] = strtolower($addr);
            }
        }

        $addresses = array_unique($addresses);

        foreach ($addresses as $emailAddress) {
            $account = $this->entityManager
                ->getRDBRepository('Account')
                ->where(['emailAddress' => $emailAddress])
                ->findOne();

            if ($account) {
                $entity->set('accountId', $account->getId());
                $entity->set('accountName', $account->get('name'));
                if (!$entity->get('parentId')) {
                    $entity->set('parentId', $account->getId());
                    $entity->set('parentType', 'Account');
                }
                $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
                return;
            }

            $contact = $this->entityManager
                ->getRDBRepository('Contact')
                ->where(['emailAddress' => $emailAddress])
                ->findOne();

            if ($contact && $contact->get('accountId')) {
                $entity->set('accountId', $contact->get('accountId'));
                if (!$entity->get('parentId')) {
                    $entity->set('parentId', $contact->getId());
                    $entity->set('parentType', 'Contact');
                }
                $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
                return;
            }
        }
    }
}
