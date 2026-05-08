<?php

namespace Espo\Custom\Hooks\Lead;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

class ValidateConversionReadiness implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $status = trim((string) ($entity->get('status') ?? ''));
        $fetchedStatus = trim((string) ($entity->getFetched('status') ?? ''));

        if (!$this->isConvertedStatus($status)) {
            return;
        }

        if ($this->isConvertedStatus($fetchedStatus)) {
            return;
        }

        $missing = [];

        $accountName = trim((string) ($entity->get('accountName') ?? ''));
        if ($accountName === '') {
            $missing[] = 'Account or Company Name';
        }

        $contactName = trim((string) ($entity->get('name') ?? ''));
        $firstName = trim((string) ($entity->get('firstName') ?? ''));
        $lastName = trim((string) ($entity->get('lastName') ?? ''));

        if ($contactName === '' && $firstName === '' && $lastName === '') {
            $missing[] = 'Contact Name';
        }

        $phone = trim((string) ($entity->get('phoneNumber') ?? ''));
        $email = trim((string) ($entity->get('emailAddress') ?? ''));

        if ($phone === '' && $email === '') {
            $missing[] = 'Phone or Email';
        }

        $leadType = trim((string) ($entity->get('leadType') ?? ''));
        if ($leadType === '') {
            $missing[] = 'Lead Type';
        }

        $targetLob = trim((string) ($entity->get('insuranceInterest') ?? ''));
        if ($targetLob === '') {
            $missing[] = 'Target Line of Business';
        }

        $assignedProducerId = trim((string) ($entity->get('assignedUserId') ?? ''));
        if ($assignedProducerId === '') {
            $missing[] = 'Assigned Producer';
        }

        $nextStep = trim((string) ($entity->get('nextStep') ?? ''));
        if ($nextStep === '') {
            $missing[] = 'Next Step';
        }

        if (!empty($missing)) {
            throw new BadRequest(
                'Lead conversion is blocked. Add: ' . implode(', ', $missing) . '.'
            );
        }
    }

    private function isConvertedStatus(string $status): bool
    {
        return in_array($status, ['Converted', 'Converted to Opportunity'], true);
    }
}
