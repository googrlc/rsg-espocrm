define('custom:views/opportunity/record/detail-view', ['views/record/detail'], function (Dep) {

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

            this.listenTo(this.model, 'sync', function () {
                this.handlePolicyStubBanner();
            }, this);

            this.listenTo(this.model, 'change:stage', function () {
                this.updateProbabilityFromStage();
                this.handleWonBound();
            }, this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.handlePolicyStubBanner();
        },

        updateProbabilityFromStage: function () {
            var stage = this.model.get('stage');
            var probabilityMap = {
                'Prospect': 10,
                'Qualify': 20,
                'Quote': 40,
                'Proposal': 60,
                'Negotiate': 75,
                'Won - Bound': 100,
                'Lost': 0
            };

            if (probabilityMap.hasOwnProperty(stage)) {
                this.model.set('probability', probabilityMap[stage]);
            }
        },

        handleWonBound: function () {
            var stage = this.model.get('stage');
            if (stage === 'Won - Bound' && !this.model.get('policyStubId')) {
                this.model.set('commissionLogged', true);
                this.model.set('onboardingSent', true);
                this.model.set('emailSequenceStarted', true);
            }
        },

        handlePolicyStubBanner: function () {
            this.$el.find('.policy-stub-banner').remove();

            var stubId = this.model.get('policyStubId');
            var stubStatus = this.model.get('policyStubStatus');

            if (stubId) {
                var statusText = stubStatus === 'Synced'
                    ? 'Policy synced from NowCerts'
                    : 'Policy stub created - Awaiting NowCerts sync';
                var bannerClass = stubStatus === 'Synced' ? 'alert-success' : 'alert-success';

                var banner = '<div class="policy-stub-banner alert ' + bannerClass + '" style="margin: 10px 0; border-radius: 6px;">' +
                    '<i class="fas fa-file-contract"></i> ' + statusText +
                    ' - <a href="#Policy/view/' + stubId + '">View policy stub</a>' +
                    '</div>';

                this.$el.find('.detail-button-container').after(banner);
            }
        },

        actionCreateTask: function () {
            var accountId = this.model.get('accountId');
            var accountName = this.model.get('accountName');
            var oppName = this.model.get('name');

            this.createView('taskModal', 'custom:views/task/record/create-modal', {
                scope: 'Task',
                sourceType: 'Opportunity',
                accountId: accountId,
                accountName: accountName,
                opportunityId: this.model.get('id'),
                opportunityName: oppName,
                contextLabel: 'Opportunity task - ' + (oppName || '') + ' - ' + (accountName || '')
            }, function (view) {
                view.render();

                this.listenToOnce(view, 'after:save', function () {
                    this.model.trigger('after:relate', 'tasks');
                }, this);
            }.bind(this));
        }
    });
});
