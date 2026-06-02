define('custom:views/task/record/detail-view', ['views/record/detail'], function (Dep) {

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

            this.listenTo(this.model, 'change:policyId', function (m, v, o) {
                if (o && o.fromPolicyAutofill) { return; }
                this.fillFromPolicy();
            });
        },

        fillFromPolicy: function () {
            var pid = this.model.get('policyId');
            if (!pid) { return; }
            var self = this;
            Espo.Ajax.getRequest('Policy/' + pid).then(function (p) {
                self.model.set({
                    policyType: p.line_of_business || p.line_of_business_raw || p.business_type || '',
                    policyNumber: p.policy_number || '',
                    carrier: p.carrier || '',
                    policyEffectiveDate: p.effective_date || null,
                    policyExpirationDate: p.expiration_date || null
                }, { fromPolicyAutofill: true });
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
