<?php
namespace Espo\Custom\Hooks\Opportunity;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class EnsureNameOnSave implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $name = trim((string) ($entity->get('name') ?? ''));

        if ($name !== '') {
            return;
        }

        $accountName = trim((string) ($entity->get('accountName') ?? ''));
        $lineOfBusiness = trim((string) ($entity->get('lineOfBusiness') ?? ''));
        $date = (new \DateTimeImmutable())->format('Y-m-d');

        $parts = [];

        if ($accountName !== '') {
            $parts[] = $accountName;
        }
        if ($lineOfBusiness !== '') {
            $parts[] = $lineOfBusiness;
        }

        $parts[] = $date;

        $entity->set('name', implode(' - ', $parts));
    }
}
