define('custom:views/policy/record/detail-view', ['views/detail'], function (DetailView) {

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
            var accountId = this.model.get('accountId');
            var accountName = this.model.get('accountName');
            var policyName = this.model.get('name');
            var carrier = this.model.get('carrier') || '';

            this.createView('taskModal', 'custom:views/task/record/create-modal', {
                scope: 'Task',
                sourceType: 'Policy',
                accountId: accountId,
                accountName: accountName,
                policyId: this.model.get('id'),
                policyName: policyName,
                contextLabel: 'Policy task - ' + policyName + (carrier ? ' - ' + carrier : '') + ' - ' + (accountName || '')
            }, function (view) {
                view.render();
            }.bind(this));
        }
    });
});
