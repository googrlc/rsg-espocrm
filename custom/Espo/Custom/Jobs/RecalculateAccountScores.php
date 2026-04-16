<?php

namespace Espo\Custom\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\ORM\EntityManager;

/**
 * Backward-compatible no-op kept so legacy scheduled-job rows don't fail after deploy.
 * It also makes a best effort to delete any lingering scheduled job entries for itself.
 */
class RecalculateAccountScores implements JobDataLess
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function run(): void
    {
        $removedCount = 0;

        try {
            $pdo = $this->entityManager->getPDO();

            foreach ([
                ['DELETE FROM scheduled_job WHERE job = ?', 'RecalculateAccountScores'],
                ['DELETE FROM scheduled_job WHERE name = ?', 'Recalculate Account Scores'],
            ] as [$sql, $value]) {
                try {
                    $statement = $pdo->prepare($sql);
                    $statement->execute([$value]);
                    $removedCount += $statement->rowCount();
                } catch (\Throwable) {
                    // Column names can vary by Espo version; ignore failed cleanup attempts.
                }
            }
        } catch (\Throwable $e) {
            if (isset($GLOBALS['log'])) {
                $GLOBALS['log']->warning(
                    'RecalculateAccountScores compatibility job could not clean up legacy schedule entries: ' . $e->getMessage()
                );
            }
        }

        if (isset($GLOBALS['log'])) {
            $message = $removedCount > 0
                ? 'RecalculateAccountScores compatibility job removed ' . $removedCount . ' legacy scheduled job row(s).'
                : 'RecalculateAccountScores compatibility job ran as a safe no-op.';

            $GLOBALS['log']->info($message);
        }
    }
}
