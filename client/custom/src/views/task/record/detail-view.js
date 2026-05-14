define('custom:views/task/record/detail-view', ['views/detail'], function (DetailView) {

    return DetailView.extend({

        setup: function () {
            DetailView.prototype.setup.call(this);

            this.addMenuItem('buttons', {
                name: 'createTask',
                label: '+ Task',
                action: 'createTask',
                style: 'warning',
                acl: 'create',
                aclScope: 'Task'
            });
        },

        actionCreateTask: function () {
            this.createView('taskModal', 'custom:views/task/record/create-modal', {
                scope: 'Task',
                sourceType: 'Account',
                accountId: null,
                accountName: null,
                contextLabel: 'New task'
            }, function (view) {
                view.render();
            }.bind(this));
        }
    });
});
