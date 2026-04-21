<?php

declare(strict_types=1);

/**
 * One-shot backfill: normalize legacy Opportunity stage values that belonged to
 * old renewal-style workflows into the current Opportunity sales pipeline stages.
 *
 * Usage:
 *   php custom/scripts/normalize-opportunity-legacy-stages.php
 *   php custom/scripts/normalize-opportunity-legacy-stages.php --dry-run
 */

use Espo\Core\Application;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\EntityManager;

require dirname(__DIR__, 2) . '/bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

$legacyMap = [
    'Renewal Notice Sent' => 'Discovery',
    'Quoted' => 'Markets Out / Shopping',
    'Presented to Client' => 'Proposal Presented',
    'Bound / Renewed' => 'Closed Won',
    'Non-Renewal / Lost' => 'Closed Lost',
];

$app = new Application();
$app->setupSystemUser();

/** @var EntityManager $em */
$em = $app->getContainer()->get('entityManager');

$updated = 0;

foreach ($legacyMap as $oldStage => $newStage) {
    $records = $em->getRDBRepository('Opportunity')
        ->where([
            'deleted' => false,
            'stage' => $oldStage,
        ])
        ->find();

    foreach ($records as $opportunity) {
        $id = (string) $opportunity->getId();
        $name = trim((string) ($opportunity->get('name') ?? ''));

        echo sprintf(
            "Opportunity %s (%s): %s -> %s\n",
            $id,
            $name !== '' ? $name : 'unnamed',
            $oldStage,
            $newStage
        );

        if (!$dryRun) {
            $opportunity->set('stage', $newStage);
            $em->saveEntity($opportunity, [SaveOption::SILENT => true]);
        }

        $updated++;
    }
}

echo sprintf(
    "\nDone%s: %d opportunities normalized.\n",
    $dryRun ? ' (dry-run)' : '',
    $updated
);
