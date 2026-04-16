<?php

namespace Espo\Custom\Hooks\Policy;

use DateInterval;
use DateTimeImmutable;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;

class ActivateAutomation implements AfterSave
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $status = (string) ($entity->get('status') ?? '');
        $fetchedStatus = (string) ($entity->getFetched('status') ?? '');

        if ($status !== 'Active' || $fetchedStatus === 'Active') {
            return;
        }

        $this->createCommissionIfMissing($entity);
        $this->createRenewalIfMissing($entity);
    }

    private function createCommissionIfMissing(Entity $policy): void
    {
        $existing = $this->entityManager
            ->getRDBRepository('Commission')
            ->where([
                'policyId' => $policy->getId(),
                'commissionType' => 'New Business',
            ])
            ->findOne();

        if ($existing) {
            return;
        }

        $premium = (float) ($policy->get('premiumAmount') ?? 0);
        $rate = $this->normalizeRate($policy->get('commissionRate'));
        $effectiveDate = $policy->get('effectiveDate');
        $expectedPaymentDate = $effectiveDate
            ? (new DateTimeImmutable($effectiveDate))->add(new DateInterval('P30D'))->format('Y-m-d')
            : null;

        $commission = $this->entityManager->getNewEntity('Commission');
        $commission->set([
            'name' => $this->buildCommissionName($policy),
            'commissionType' => 'New Business',
            'status' => 'Estimated',
            'policyId' => $policy->getId(),
            'policyName' => $policy->get('name'),
            'accountId' => $policy->get('accountId'),
            'accountName' => $policy->get('accountName'),
            'contactId' => $policy->get('contactId'),
            'contactName' => $policy->get('contactName'),
            'assignedUserId' => $policy->get('assignedUserId'),
            'assignedUserName' => $policy->get('assignedUserName'),
            'producer' => $policy->get('assignedUserName'),
            'carrier' => $policy->get('carrier'),
            'lineOfBusiness' => $this->normalizeLineOfBusiness($policy->get('lineOfBusiness')),
            'writtenPremium' => $premium,
            'commissionRate' => $rate,
            'estimatedCommission' => round($premium * $rate, 2),
            'effectiveDate' => $effectiveDate,
            'expectedPaymentDate' => $expectedPaymentDate,
        ]);

        $this->entityManager->saveEntity($commission);
    }

    private function createRenewalIfMissing(Entity $policy): void
    {
        $existing = $this->entityManager
            ->getRDBRepository('Renewal')
            ->where(['policyId' => $policy->getId()])
            ->findOne();

        if ($existing) {
            return;
        }

        $expirationDate = $policy->get('expirationDate');

        $renewal = $this->entityManager->getNewEntity('Renewal');
        $renewal->set([
            'name' => $this->buildRenewalName($policy),
            'stage' => 'Identified',
            'policyId' => $policy->getId(),
            'policyName' => $policy->get('name'),
            'accountId' => $policy->get('accountId'),
            'accountName' => $policy->get('accountName'),
            'contactId' => $policy->get('contactId'),
            'contactName' => $policy->get('contactName'),
            'assignedUserId' => $policy->get('assignedUserId'),
            'assignedUserName' => $policy->get('assignedUserName'),
            'expirationDate' => $expirationDate,
            'renewalEffectiveDate' => $expirationDate,
            'currentPremium' => $policy->get('premiumAmount'),
            'lineOfBusiness' => $this->normalizeLineOfBusiness($policy->get('lineOfBusiness')),
            'carrier' => $policy->get('carrier'),
            'commissionRate' => $this->normalizeRate($policy->get('commissionRate')),
        ]);

        $this->entityManager->saveEntity($renewal);
    }

    private function buildCommissionName(Entity $policy): string
    {
        $accountName = (string) ($policy->get('accountName') ?? 'Client');

        return $accountName . ' - Commission';
    }

    private function buildRenewalName(Entity $policy): string
    {
        $accountName = (string) ($policy->get('accountName') ?? 'Account');
        $lineOfBusiness = (string) ($policy->get('lineOfBusiness') ?? $policy->get('businessType') ?? 'Policy');

        return $accountName . ' - ' . $lineOfBusiness . ' Renewal';
    }

    private function normalizeRate(mixed $rate): float
    {
        if ($rate === null || $rate === '') {
            return 0.12;
        }

        $numericRate = (float) $rate;

        return $numericRate > 1 ? $numericRate / 100 : $numericRate;
    }

    private function normalizeLineOfBusiness(mixed $value): string
    {
        $line = trim((string) $value);

        return match ($line) {
            'GL' => 'General Liability',
            'Auto' => 'Personal Auto',
            'Home' => 'Homeowners',
            default => $line === '' ? 'Other' : $line,
        };
    }
}
