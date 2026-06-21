/************************************************************************
 * RSG "Archive" action handler (detail / detail-small buttons)
 *
 * Wired from clientDefs/Task.json:
 *   menu.detail.buttons + modalDetailActionList
 *     -> handler: custom:handlers/task-archive-handler
 *        actionFunction: archiveTask
 *        checkVisibilityFunction: isArchiveTaskVisible
 *
 * Archiving parks a task: status -> "Archived". It drops off every open-task
 * filter (notActualOptions) and the Account "Activities" panel, and lands in
 * the Account "History" panel (historyStatusList) so it stays with the record.
 * Unlike "Complete", archiving is NOT gated by the confirmation checklist.
 *
 * Base "action-handler" provides this.view (the record view).
 ************************************************************************/

define('custom:handlers/task-archive-handler', ['action-handler'], function (Dep) {

    return Dep.extend({

        // Only offer Archive on still-open tasks.
        isArchiveTaskVisible: function () {
            var status = this.view.model.get('status');

            return ['Completed', 'Cancelled', 'Archived'].indexOf(status) === -1;
        },

        archiveTask: function () {
            var view = this.view;
            var model = view.model;

            view.confirm({
                message: 'Archive this task? It drops off the open-task lists but stays on the account under History.',
                confirmText: 'Archive'
            }, function () {
                model.save({status: 'Archived'}, {patch: true}).then(function () {
                    Espo.Ui.success('Task archived');

                    // Refresh so the new status label + stream note appear.
                    if (typeof model.fetch === 'function') {
                        model.fetch();
                    }
                });
            });
        }
    });
});
