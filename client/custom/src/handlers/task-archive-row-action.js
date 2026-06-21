/************************************************************************
 * RSG "Archive" row action (list view + relationship panels)
 *
 * Wired from clientDefs/Task.json:
 *   rowActionList: ["complete", "archive"]
 *   rowActionDefs.archive.handler: custom:handlers/task-archive-row-action
 * and from each parent panel:
 *   clientDefs.{Account|Contact}.relationshipPanels.tasks.rowActionList
 *
 * Per EspoCRM's row-action contract the module returns a plain class that
 * is constructed with the list view and must expose:
 *   isAvailable(model, action) -> boolean   (controls per-row visibility)
 *   process(model, action)                  (runs on click)
 ************************************************************************/

define('custom:handlers/task-archive-row-action', [], function () {

    class TaskArchiveRowAction {

        constructor(listView) {
            this.listView = listView;
        }

        isAvailable(model) {
            return ['Completed', 'Cancelled', 'Archived'].indexOf(model.get('status')) === -1;
        }

        process(model) {
            var listView = this.listView;

            listView.confirm({
                message: 'Archive this task? It drops off the open-task lists but stays on the account under History.',
                confirmText: 'Archive'
            }, function () {
                model.save({status: 'Archived'}, {patch: true}).then(function () {
                    Espo.Ui.success('Task archived');

                    // Reload so the archived task drops off open-task filters.
                    if (listView.collection && typeof listView.collection.fetch === 'function') {
                        listView.collection.fetch();
                    }
                });
            });
        }
    }

    return TaskArchiveRowAction;
});
