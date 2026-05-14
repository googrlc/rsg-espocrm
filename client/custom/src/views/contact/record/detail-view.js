define('custom:views/contact/record/detail-view', ['crm:views/contact/detail'], function (ContactDetailView) {

    return ContactDetailView.extend({

        setup: function () {
            ContactDetailView.prototype.setup.call(this);

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
