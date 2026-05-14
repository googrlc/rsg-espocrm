<?php
/*************************************************************************
 * Migration Job: Backfill Task.account_id from Task.linkedAccount
 * 
 * Production has 7 live tasks with linked_account_id but no account_id.
 * This migration ensures those tasks remain visible in Account task views
 * after we remove the linkedAccount field dependency.
 *
 * Run BEFORE deploying the metadata change that removes linkedAccount.
 ************************************************************************/

namespace Espo\Custom\Jobs;

use Espo\Core\ORM\Entity;
use Espo\Core\Jobs\Job as BaseJob;

class MigrateTaskAccountRollup extends BaseJob
{
    public function run()
    {
        $this->getContainer()->get('logger')->info('[MigrateTaskAccountRollup] Starting migration');

        $taskList = $this->getEntityManager()
            ->getRepository('Task')
            ->where([
                'linkedAccountId!=' => null,
                'accountId' => null
            ])
            ->find();

        $count = 0;
        $updated = 0;

        foreach ($taskList as $task) {
            $count++;
            
            // Get the linked account
            $linkedAccount = $task->get('linkedAccount');
            if (!$linkedAccount) {
                $this->getContainer()->get('logger')->warning(
                    '[MigrateTaskAccountRollup] Task ' . $task->id . 
                    ' has linkedAccountId but no related Account found'
                );
                continue;
            }

            // Set account_id to match linkedAccount
            $task->set('accountId', $linkedAccount->id);
            
            try {
                $this->getEntityManager()->saveEntity($task);
                $updated++;
                $this->getContainer()->get('logger')->info(
                    '[MigrateTaskAccountRollup] Updated Task ' . $task->id . 
                    ': accountId = ' . $linkedAccount->id
                );
            } catch (\Exception $e) {
                $this->getContainer()->get('logger')->error(
                    '[MigrateTaskAccountRollup] Failed to update Task ' . $task->id . 
                    ': ' . $e->getMessage()
                );
            }
        }

        $this->getContainer()->get('logger')->info(
            '[MigrateTaskAccountRollup] Complete: Found ' . $count . 
            ' tasks, updated ' . $updated
        );

        return true;
    }
}
