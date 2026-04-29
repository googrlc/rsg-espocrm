<?php

declare(strict_types=1);

/**
 * One-shot backfill: replace placeholder policy/renewal accountName values
 * (e.g. Unknown Client) with the linked Account.name, then rebuild display names.
 *
 * Usage (production container):
 *   php custom/scripts/repair-placeholder-account-names.php
 *   php custom/scripts/repair-placeholder-account-names.php --dry-run
 */

use Espo\Core\Application;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Custom\Classes\Account\AccountNameResolution;
use Espo\ORM\EntityManager;

require dirname(__DIR__, 2) . '/bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

$app = new Application();
$app->setupSystemUser();

/** @var EntityManager $em */
$em = $app->getContainer()->get('entityManager');

$policyCount = 0;
$renewalCount = 0;

foreach ($em->getRDBRepository('Policy')->where(['deleted' => false])->find() as $policy) {
    if (!$policy->get('accountId')) {
        continue;
    }

    $current = trim((string) ($policy->get('accountName') ?? ''));
    $resolved = AccountNameResolution::resolveForPolicy($em, $policy);

    if ($resolved === '') {
        continue;
    }

    if ($current !== '' && !AccountNameResolution::isPlaceholder($current)) {
        continue;
    }

    if ($current === $resolved) {
        continue;
    }

    $lineOfBusiness = $policy->get('line_of_business') ?: $policy->get('business_type') ?: '';
    $policyNumber = $policy->get('policy_number') ?: '';
    $parts = array_values(array_filter([$resolved, $lineOfBusiness, $policyNumber]));
    $newPolicyName = $parts !== [] ? implode(' | ', $parts) : (string) ($policy->get('name') ?? '');

    echo sprintf(
        "Policy %s: accountName %s -> %s\n",
        $policy->getId(),
        $current === '' ? '(empty)' : $current,
        $resolved
    );

    if (!$dryRun) {
        $policy->set('accountName', $resolved);
        if ($parts !== []) {
            $policy->set('name', $newPolicyName);
        }
        $em->saveEntity($policy, [SaveOption::SILENT => true]);
    }

    $policyCount++;
}

foreach ($em->getRDBRepository('Renewal')->where(['deleted' => false])->find() as $renewal) {
    if (!$renewal->get('accountId')) {
        continue;
    }

    $raw = trim((string) ($renewal->get('accountName') ?? ''));
    $resolved = AccountNameResolution::resolveForRenewal($em, $renewal);

    $nextAccountName = $raw;
    if ($resolved !== '' && ($raw === '' || AccountNameResolution::isPlaceholder($raw))) {
        $nextAccountName = $resolved;
    }

    $newName = buildRenewalTitle(
        trim($nextAccountName) !== '' ? trim($nextAccountName) : 'Account',
        normalizeLineOfBusiness($renewal->get('line_of_business'))
    );

    $accountChanged = $nextAccountName !== $raw;
    $nameChanged = $newName !== (string) ($renewal->get('name') ?? '');

    if (!$accountChanged && !$nameChanged) {
        continue;
    }

    $msg = sprintf('Renewal %s:', $renewal->getId());
    if ($accountChanged) {
        $msg .= sprintf(" accountName %s -> %s;", $raw === '' ? '(empty)' : $raw, $nextAccountName);
    }
    if ($nameChanged) {
        $msg .= sprintf(" name -> %s", $newName);
    }
    echo $msg . "\n";

    if (!$dryRun) {
        $renewal->set('accountName', $nextAccountName);
        $renewal->set('name', $newName);
        $em->saveEntity($renewal, [SaveOption::SILENT => true]);
    }

    $renewalCount++;
}

echo sprintf(
    "\nDone%s: %d policies, %d renewals updated.\n",
    $dryRun ? ' (dry-run)' : '',
    $policyCount,
    $renewalCount
);

function normalizeLineOfBusiness(mixed $value): string
{
    $line = trim((string) $value);

    return match ($line) {
        'GL' => 'General Liability',
        'Auto' => 'Personal Auto',
        'Home' => 'Homeowners',
        default => $line === '' ? 'Other' : $line,
    };
}

function buildRenewalTitle(string $accountName, string $lineOfBusiness): string
{
    $accountName = trim($accountName) !== '' ? trim($accountName) : 'Account';
    $lineOfBusiness = trim($lineOfBusiness) !== '' ? trim($lineOfBusiness) : 'Policy';

    return $accountName . ' - ' . $lineOfBusiness . ' Renewal';
}
