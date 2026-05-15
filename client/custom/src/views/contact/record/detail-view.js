define('custom:views/contact/record/detail-view', ['views/record/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.addButton({
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
            var contactName = this.model.get('name');

            this.createView('taskModal', 'custom:views/task/record/create-modal', {
                scope: 'Task',
                sourceType: 'Contact',
                accountId: accountId,
                accountName: accountName,
                contactId: this.model.get('id'),
                contactName: contactName,
                contextLabel: 'Contact task - ' + contactName + ' - ' + (accountName || '')
            }, function (view) {
                view.render();
            }.bind(this));
        }
    });
});
