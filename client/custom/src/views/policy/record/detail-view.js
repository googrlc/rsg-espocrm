define('custom:views/policy/record/detail-view', ['views/record/detail'], function (Dep) {

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

            this.addButton({
                name: 'createCrossSell',
                label: 'Cross-Sell',
                action: 'createCrossSell',
                style: 'primary',
                acl: 'create',
                aclScope: 'Opportunity'
            });

            this.addButton({
                name: 'createEndorsement',
                label: 'Endorsement',
                action: 'createEndorsement',
                style: 'default',
                acl: 'create',
                aclScope: 'Opportunity'
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
        },

        actionCreateCrossSell: function () {
            this._createOpportunity('Cross-Sell');
        },

        actionCreateEndorsement: function () {
            this._createOpportunity('Endorsement');
        },

        _createOpportunity: function (businessType) {
            var self = this;
            var accountId = this.model.get('accountId');
            var accountName = this.model.get('accountName');
            var lineOfBusiness = this.model.get('line_of_business') || '';
            var policyNumber = this.model.get('policy_number') || '';
            var carrier = this.model.get('carrier') || '';

            var oppName = businessType === 'Cross-Sell'
                ? 'Cross-Sell - ' + (accountName || '') + ' - ' + lineOfBusiness
                : 'Endorsement - ' + (accountName || '') + ' - ' + policyNumber;

            var attributes = {
                name: oppName,
                accountId: accountId,
                accountName: accountName,
                businessType: businessType,
                lineOfBusiness: lineOfBusiness,
                leadSource: 'Existing Customer',
                currentCarrier: carrier,
                priority: 'Warm',
                stage: 'Discovery'
            };

            this.createView('createOppModal', 'views/modals/edit', {
                scope: 'Opportunity',
                attributes: attributes
            }, function (view) {
                view.render();

                self.listenToOnce(view, 'after:save', function () {
                    var bottomView = self.getView('bottom');

                    if (bottomView) {
                        var oppPanel = bottomView.getView('opportunities');

                        if (oppPanel) {
                            oppPanel.actionRefresh();
                        }
                    }
                });
            });
        }
    });
});
