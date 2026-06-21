<?php

namespace Espo\Custom\Jobs;

use DateTimeImmutable;
use Espo\Core\Job\JobDataLess;
use Espo\ORM\EntityManager;

/**
 * Auto-archives tasks that have been "Completed" for 7+ days.
 *
 * Completed tasks already drop off the open-task lists, but they accumulate in
 * the "Completed" view forever. This nightly sweep moves anything completed at
 * least 7 days ago to status "Archived" so the long-term home for finished work
 * is the Archived bucket (account History panel), while recent completions stay
 * visible under the "Completed" filter for review.
 *
 * Window source: dateCompleted when present; modifiedAt as a fallback for legacy
 * rows completed before dateCompleted was maintained.
 *
 * Saves with skipHooks: this is system housekeeping, not an interactive edit, so
 * it must bypass the Task beforeSave hooks:
 *  - NormalizeAssignmentAndAccount rejects tasks whose assignee is not
 *    Lamar/Gretchen (stale or system-owned completed tasks would abort the run).
 *  - SendServiceWebhook would re-run per task (no archive webhook exists anyway).
 * Skipping hooks also means dateCompleted is left untouched (preserved) — the
 * SetCompletedDate "clear on leaving Completed" branch never fires here. The
 * interactive Archive button keeps full hooks (and SetCompletedDate now preserves
 * dateCompleted on Completed -> Archived for that path).
 *
 * Registered in Resources/metadata/app/scheduledJobs.json (daily, isSystem).
 */
class ArchiveCompletedTasks implements JobDataLess
{
    private const ARCHIVE_AFTER_DAYS = 7;

    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function run(): void
    {
        $cutoff = (new DateTimeImmutable('-' . self::ARCHIVE_AFTER_DAYS . ' days'))
            ->format('Y-m-d H:i:s');

        $tasks = $this->entityManager
            ->getRDBRepository('Task')
            ->sth()
            ->where([
                'status' => 'Completed',
                'deleted' => false,
                'OR' => [
                    ['dateCompleted<=' => $cutoff],
                    ['dateCompleted' => null, 'modifiedAt<=' => $cutoff],
                ],
            ])
            ->find();

        $count = 0;
        foreach ($tasks as $task) {
            $task->set('status', 'Archived');
            $this->entityManager->saveEntity($task, ['skipHooks' => true]);
            $count++;
        }

        $GLOBALS['log']->info(
            'ArchiveCompletedTasks: archived ' . $count
            . ' task(s) completed on/before ' . $cutoff . '.'
        );
    }
}
