<?php

namespace Espo\Custom\Classes\ActivityLog;

use DateInterval;
use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

abstract class BaseTriageManager
{
    public function __construct(
        protected EntityManager $entityManager
    ) {}

    protected function resolveOwnership(Entity $activityLog, ?Entity $policy, ?Entity $account): array
    {
        $assignedUserId = $activityLog->get('assignedUserId');
        $assignedUserName = $activityLog->get('assignedUserName');

        if (!$assignedUserId && $policy) {
            $assignedUserId = $policy->get('assignedUserId');
            $assignedUserName = $policy->get('assignedUserName');
        }

        if (!$assignedUserId && $account) {
            $assignedUserId = $account->get('assignedUserId');
            $assignedUserName = $account->get('assignedUserName');
        }

        return [$assignedUserId, $assignedUserName];
    }

    protected function resolveParent(Entity $activityLog, ?Entity $policy): array
    {
        if ($activityLog->get('policyId')) {
            return [
                'Policy',
                $activityLog->get('policyId'),
                $activityLog->get('policyName') ?: ($policy?->get('name') ?? ''),
                'Policy',
            ];
        }

        if ($activityLog->get('contactId')) {
            return [
                'Contact',
                $activityLog->get('contactId'),
                $activityLog->get('contactName'),
                'Contact',
            ];
        }

        return [
            'Account',
            $activityLog->get('accountId'),
            $activityLog->get('accountName'),
            'Account',
        ];
    }

    protected function addBusinessDays(DateTimeImmutable $date, int $days): DateTimeImmutable
    {
        if ($days === 0) {
            return $this->shiftWeekendToMonday($date);
        }

        $added = 0;
        while ($added < $days) {
            $date = $date->add(new DateInterval('P1D'));
            if ((int) $date->format('N') < 6) {
                $added++;
            }
        }

        return $date;
    }

    private function shiftWeekendToMonday(DateTimeImmutable $date): DateTimeImmutable
    {
        while ((int) $date->format('N') > 5) {
            $date = $date->add(new DateInterval('P1D'));
        }

        return $date;
    }
}
