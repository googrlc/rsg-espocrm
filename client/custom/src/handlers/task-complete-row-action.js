/************************************************************************
 * RSG "Complete Task" row action (list view + relationship panels)
 *
 * Wired from clientDefs/Task.json:
 *   rowActionList: ["complete"]
 *   rowActionDefs.complete.handler: custom:handlers/task-complete-row-action
 * and from each parent panel:
 *   clientDefs.{Account|Contact|Policy}.relationshipPanels.tasks.rowActionList
 *
 * Per EspoCRM's row-action contract the module returns a plain class that
 * is constructed with the list view and must expose:
 *   isAvailable(model, action) -> boolean   (controls per-row visibility)
 *   process(model, action)                  (runs on click)
 ************************************************************************/

define('custom:handlers/task-complete-row-action', [], function () {

    class TaskCompleteRowAction {

        constructor(listView) {
            this.listView = listView;
        }

        isAvailable(model) {
            return ['Completed', 'Cancelled'].indexOf(model.get('status')) === -1;
        }

        process(model) {
            var listView = this.listView;

            listView.createView('taskCompleteModal', 'custom:views/modals/task-complete', {
                model: model
            }, function (modal) {
                modal.render();

                listView.listenToOnce(modal, 'completed', function () {
                    // Reload the list/panel so the completed task drops off
                    // open-task filters and the status updates everywhere.
                    if (listView.collection && typeof listView.collection.fetch === 'function') {
                        listView.collection.fetch();
                    }
                });
            });
        }
    }

    return TaskCompleteRowAction;
});
