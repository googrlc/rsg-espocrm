<?php

namespace Espo\Custom\Classes\Account;

use DateInterval;
use DateTimeImmutable;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class AccountPlaybookManager
{
    private const ACTIVE_POLICY_STATUSES = [
        'Active',
        'Up for Renewal',
        'Renewing',
        'Renewed',
    ];

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function syncForAccount(Entity $account): void
    {
        $accountId = (string) ($account->getId() ?? '');
        if ($accountId === '') {
            return;
        }

        $policyList = $this->entityManager
            ->getRDBRepository('Policy')
            ->where(['accountId' => $accountId])
            ->find();
        $activityList = $this->entityManager
            ->getRDBRepository('ActivityLog')
            ->where(['accountId' => $accountId])
            ->find();

        $coverageLines = $this->collectCoverageLines($account, $policyList);
        $carrierMixCount = $this->countCarriers($account, $policyList);
        $annualPremium = max(
            (float) ($account->get('annualPremium') ?? 0),
            (float) ($account->get('totalActivePremium') ?? 0)
        );
        $recentChanges = $this->collectRecentChanges($activityList);

        foreach ($this->buildPlaybooks($account, $coverageLines, $carrierMixCount, $annualPremium, $recentChanges) as $playbook) {
            $this->createTaskIfMissing($account, $playbook);
        }
    }

    private function buildPlaybooks(
        Entity $account,
        array $coverageLines,
        int $carrierMixCount,
        float $annualPremium,
        array $recentChanges
    ): array {
        $accountName = trim((string) ($account->get('name') ?? 'Account'));
        $coverageSummary = $coverageLines === [] ? 'No active lines on file' : implode(', ', $coverageLines);
        $hasUmbrella = in_array('Umbrella', $coverageLines, true);
        $hasLife = in_array('Life', $coverageLines, true);
        $hasMedicare = in_array('Medicare', $coverageLines, true);
        $hasAuto = in_array('Auto', $coverageLines, true);
        $hasHome = in_array('Home', $coverageLines, true);
        $hasCommercialCore = count(array_intersect($coverageLines, ['Commercial Auto', 'GL', 'BOP', 'Transportation'])) > 0;

        $playbooks = [];

        if ((bool) $account->get('gapUmbrella') || (!$hasUmbrella && ($hasAuto || $hasHome || $hasCommercialCore))) {
            $playbooks[] = [
                'automationKey' => 'account-playbook:umbrella',
                'name' => 'Cross-Sell Review: Umbrella - ' . $accountName,
                'urgency' => $recentChanges['hasRecentCoverageChange'] || $annualPremium >= 5000 ? 'High' : 'Normal',
                'dueDays' => $recentChanges['hasRecentCoverageChange'] ? 2 : 5,
                'reason' => 'Umbrella review triggered from account LOB mix and coverage gap signals.',
                'description' => $this->buildDescription([
                    'Playbook: Umbrella',
                    'Current lines: ' . $coverageSummary,
                    'Annual premium: ' . number_format($annualPremium, 2),
                    'Carrier mix: ' . $carrierMixCount,
                    'Trigger: Umbrella gap flag or missing umbrella with core lines on file.',
                ]),
            ];
        }

        if ((bool) $account->get('gapLife') || (!$hasLife && ($hasAuto || $hasHome || $hasMedicare))) {
            $playbooks[] = [
                'automationKey' => 'account-playbook:life',
                'name' => 'Cross-Sell Review: Life Insurance - ' . $accountName,
                'urgency' => $annualPremium >= 3000 ? 'High' : 'Normal',
                'dueDays' => 5,
                'reason' => 'Life review triggered from household coverage mix and life gap signals.',
                'description' => $this->buildDescription([
                    'Playbook: Life Insurance',
                    'Current lines: ' . $coverageSummary,
                    'Annual premium: ' . number_format($annualPremium, 2),
                    'Trigger: Life gap flag or household account without life coverage.',
                ]),
            ];
        }

        $medicareEligibilityDate = trim((string) ($account->get('gapMedicareEligible') ?? ''));
        if (
            (bool) $account->get('gapMedicare') ||
            (!$hasMedicare && $this->isWithinDays($medicareEligibilityDate, 180))
        ) {
            $playbooks[] = [
                'automationKey' => 'account-playbook:medicare',
                'name' => 'Cross-Sell Review: Medicare - ' . $accountName,
                'urgency' => $this->isWithinDays($medicareEligibilityDate, 90) ? 'High' : 'Normal',
                'dueDays' => $this->isWithinDays($medicareEligibilityDate, 90) ? 3 : 7,
                'reason' => 'Medicare review triggered from eligibility timing or gap signals.',
                'description' => $this->buildDescription([
                    'Playbook: Medicare',
                    'Current lines: ' . $coverageSummary,
                    'Medicare eligible: ' . ($medicareEligibilityDate !== '' ? $medicareEligibilityDate : 'Not set'),
                    'Trigger: Medicare gap flag or eligibility within 180 days.',
                ]),
            ];
        }

        if ($carrierMixCount >= 2 && $annualPremium >= 5000) {
            $playbooks[] = [
                'automationKey' => 'account-playbook:carrier-review',
                'name' => 'Cross-Sell Review: Carrier Consolidation - ' . $accountName,
                'urgency' => $annualPremium >= 10000 ? 'High' : 'Normal',
                'dueDays' => 5,
                'reason' => 'Carrier mix and premium volume support a proactive account review.',
                'description' => $this->buildDescription([
                    'Playbook: Carrier Consolidation',
                    'Current lines: ' . $coverageSummary,
                    'Annual premium: ' . number_format($annualPremium, 2),
                    'Carrier mix: ' . $carrierMixCount,
                    'Trigger: Multi-carrier account with meaningful premium volume.',
                ]),
            ];
        }

        if ($recentChanges['hasRecentCoverageChange']) {
            $playbooks[] = [
                'automationKey' => 'account-playbook:change-review',
                'name' => 'Cross-Sell Review: Post-Change Coverage Review - ' . $accountName,
                'urgency' => 'High',
                'dueDays' => 2,
                'reason' => 'Recent premium or coverage changes should trigger a proactive review.',
                'description' => $this->buildDescription([
                    'Playbook: Post-Change Coverage Review',
                    'Current lines: ' . $coverageSummary,
                    'Annual premium: ' . number_format($annualPremium, 2),
                    'Recent changes: ' . ($recentChanges['summary'] !== '' ? $recentChanges['summary'] : 'Recent policy change detected'),
                ]),
            ];
        }

        return $playbooks;
    }

    private function createTaskIfMissing(Entity $account, array $playbook): void
    {
        $existingTask = $this->entityManager
            ->getRDBRepository('Task')
            ->where([
                'linkedAccountId' => $account->getId(),
                'automationKey' => $playbook['automationKey'],
                'status!=' => ['Completed', 'Cancelled'],
            ])
            ->findOne();

        if ($existingTask) {
            return;
        }

        $task = $this->entityManager->getNewEntity('Task');
        $task->set([
            'name' => $playbook['name'],
            'status' => 'Inbox',
            'taskType' => 'New Business',
            'taskSource' => 'Account',
            'urgency' => $playbook['urgency'],
            'syncSource' => 'Manual',
            'linkedAccountId' => $account->getId(),
            'linkedAccountName' => $account->get('name'),
            'accountId' => $account->getId(),
            'accountName' => $account->get('name'),
            'assignedUserId' => $account->get('assignedUserId'),
            'assignedUserName' => $account->get('assignedUserName'),
            'teamsIds' => $account->get('teamsIds') ?? [],
            'parentType' => 'Account',
            'parentId' => $account->getId(),
            'parentName' => $account->get('name'),
            'triageSummary' => $playbook['name'],
            'triageReason' => $playbook['reason'],
            'description' => $playbook['description'],
            'dateEndDate' => $this->calculateDueDate((int) $playbook['dueDays']),
            'automationKey' => $playbook['automationKey'],
        ]);

        $this->entityManager->saveEntity($task);
    }

    private function collectCoverageLines(Entity $account, iterable $policyList): array
    {
        $lines = [];

        foreach ($policyList as $policy) {
            $status = trim((string) ($policy->get('status') ?? ''));
            if (!in_array($status, self::ACTIVE_POLICY_STATUSES, true)) {
                continue;
            }

            $line = trim((string) ($policy->get('lineOfBusiness') ?? $policy->get('businessType') ?? ''));
            if ($line !== '') {
                $lines[$line] = true;
            }
        }

        foreach ($this->normalizeMultiEnum($account->get('lob')) as $line) {
            $lines[$line] = true;
        }

        $normalized = array_keys($lines);
        sort($normalized);

        return $normalized;
    }

    private function countCarriers(Entity $account, iterable $policyList): int
    {
        $carriers = [];

        foreach ($policyList as $policy) {
            $status = trim((string) ($policy->get('status') ?? ''));
            if (!in_array($status, self::ACTIVE_POLICY_STATUSES, true)) {
                continue;
            }

            $carrier = trim((string) ($policy->get('carrier') ?? ''));
            if ($carrier !== '') {
                $carriers[$carrier] = true;
            }
        }

        if ($carriers === []) {
            foreach ($this->extractCarrierValues((string) ($account->get('carrier') ?? '')) as $carrier) {
                $carriers[$carrier] = true;
            }
        }

        return count($carriers);
    }

    private function collectRecentChanges(iterable $activityList): array
    {
        $today = new DateTimeImmutable('today');
        $summaries = [];

        foreach ($activityList as $activity) {
            $loggedAt = $this->toDate((string) ($activity->get('dateTime') ?? ''));
            if (!$loggedAt) {
                continue;
            }

            $daysAgo = (int) $loggedAt->diff($today)->format('%r%a');
            if ($daysAgo > 30) {
                continue;
            }

            $activityType = trim((string) ($activity->get('activityType') ?? ''));
            if (!in_array($activityType, ['Premium Change', 'Coverage Remove', 'Cancellation'], true)) {
                continue;
            }

            $summary = trim((string) ($activity->get('changeSummary') ?? ''));
            $summaries[] = $summary !== '' ? $summary : $activityType;
        }

        return [
            'hasRecentCoverageChange' => $summaries !== [],
            'summary' => implode(' | ', array_slice($summaries, 0, 3)),
        ];
    }

    private function calculateDueDate(int $businessDays): string
    {
        $date = new DateTimeImmutable('today');
        $addedDays = 0;

        while ($addedDays < $businessDays) {
            $date = $date->add(new DateInterval('P1D'));
            if ((int) $date->format('N') < 6) {
                $addedDays++;
            }
        }

        if ($businessDays === 0) {
            return $date->format('Y-m-d');
        }

        while ((int) $date->format('N') > 5) {
            $date = $date->add(new DateInterval('P1D'));
        }

        return $date->format('Y-m-d');
    }

    private function isWithinDays(string $dateValue, int $days): bool
    {
        if ($dateValue === '') {
            return false;
        }

        $today = new DateTimeImmutable('today');
        $target = new DateTimeImmutable($dateValue);
        $daysUntil = (int) $today->diff($target)->format('%r%a');

        return $daysUntil >= 0 && $daysUntil <= $days;
    }

    private function buildDescription(array $lines): string
    {
        return implode("\n", array_filter($lines));
    }

    private function normalizeMultiEnum(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $value
        )));
    }

    private function extractCarrierValues(string $value): array
    {
        $parts = preg_split('/[;,|]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function toDate(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        return new DateTimeImmutable(substr(str_replace('T', ' ', $value), 0, 10));
    }
}
