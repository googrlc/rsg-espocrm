/************************************************************************
 * RSG "Complete Task" action handler (detail / detail-small buttons)
 *
 * Wired from clientDefs/Task.json:
 *   menu.detail.buttons + modalDetailActionList
 *     -> handler: custom:handlers/task-complete-handler
 *        actionFunction: completeTask
 *        checkVisibilityFunction: isCompleteTaskVisible
 *
 * Base "action-handler" provides this.view (the record view).
 ************************************************************************/

define('custom:handlers/task-complete-handler', ['action-handler'], function (Dep) {

    return Dep.extend({

        // Hide the button once the task is already closed.
        isCompleteTaskVisible: function () {
            var status = this.view.model.get('status');

            return ['Completed', 'Cancelled'].indexOf(status) === -1;
        },

        completeTask: function () {
            var view = this.view;
            var model = view.model;

            view.createView('taskCompleteModal', 'custom:views/modals/task-complete', {
                model: model
            }, function (modal) {
                modal.render();

                view.listenToOnce(modal, 'completed', function () {
                    // Refresh so the new status label + stream note appear.
                    if (typeof model.fetch === 'function') {
                        model.fetch();
                    }
                });
            });
        }
    });
});
