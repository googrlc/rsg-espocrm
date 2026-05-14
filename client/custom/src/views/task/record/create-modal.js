define('custom:views/task/record/create-modal', ['views/modal', 'model-factory'], function (ModalView, ModelFactory) {

    return ModalView.extend({

        template: 'custom:task/record/create-modal',

        className: 'dialog',

        backdrop: true,

        cssName: 'task-create-modal',

        shortcutKeys: {
            'Control+Enter': 'save',
        },

        data: function () {
            return {
                contextLabel: this.options.contextLabel || '',
                sourceType: this.options.sourceType || 'Account'
            };
        },

        setup: function () {
            this.headerText = 'Create Task';
            this.buttonList = [
                {
                    name: 'save',
                    label: 'Save',
                    style: 'primary'
                },
                {
                    name: 'cancel',
                    label: 'Cancel'
                }
            ];

            this.wait(
                new Promise(function (resolve) {
                    ModelFactory.create('Task', function (model) {
                        this.taskModel = model;

                        model.set('taskSource', this.options.sourceType || 'Account');
                        model.set('status', 'Inbox');
                        model.set('priority', 'Normal');

                        if (this.options.accountId) {
                            model.set('accountId', this.options.accountId);
                            model.set('accountName', this.options.accountName);
                            model.set('linkedAccountId', this.options.accountId);
                            model.set('linkedAccountName', this.options.accountName);
                        }

                        if (this.options.contactId) {
                            model.set('contactId', this.options.contactId);
                            model.set('contactName', this.options.contactName);
                        }

                        if (this.options.parentType && this.options.parentId) {
                            model.set('parentType', this.options.parentType);
                            model.set('parentId', this.options.parentId);
                            model.set('parentName', this.options.parentName || '');
                        } else if (this.options.opportunityId) {
                            model.set('parentType', 'Opportunity');
                            model.set('parentId', this.options.opportunityId);
                            model.set('parentName', this.options.opportunityName);
                        } else if (this.options.policyId) {
                            model.set('parentType', 'Policy');
                            model.set('parentId', this.options.policyId);
                            model.set('parentName', this.options.policyName);
                        } else if (this.options.contactId) {
                            model.set('parentType', 'Contact');
                            model.set('parentId', this.options.contactId);
                            model.set('parentName', this.options.contactName);
                        } else if (this.options.accountId) {
                            model.set('parentType', 'Account');
                            model.set('parentId', this.options.accountId);
                            model.set('parentName', this.options.accountName);
                        }

                        this.createView('name', 'views/fields/varchar', {
                            selector: '.field[data-name="name"]',
                            model: model,
                            name: 'name',
                            mode: 'edit',
                            params: {required: true}
                        });

                        this.createView('assignedUser', 'custom:views/task/fields/assigned-user', {
                            selector: '.field[data-name="assignedUser"]',
                            model: model,
                            name: 'assignedUser',
                            mode: 'edit',
                            params: {required: true},
                            foreignScope: 'User'
                        });

                        this.createView('dateEnd', 'views/fields/date', {
                            selector: '.field[data-name="dateEnd"]',
                            model: model,
                            name: 'dateEnd',
                            mode: 'edit',
                            params: {required: true}
                        });

                        this.createView('priority', 'views/fields/enum', {
                            selector: '.field[data-name="priority"]',
                            model: model,
                            name: 'priority',
                            mode: 'edit',
                            params: {
                                options: ['Normal', 'High', 'Urgent']
                            }
                        });

                        this.createView('description', 'views/fields/text', {
                            selector: '.field[data-name="description"]',
                            model: model,
                            name: 'description',
                            mode: 'edit',
                            params: {rows: 3}
                        });

                        resolve();
                    }.bind(this));
                }.bind(this))
            );
        },

        actionSave: function () {
            var nameView = this.getView('name');
            var assignedView = this.getView('assignedUser');
            var dateView = this.getView('dateEnd');

            var notValid = false;
            if (nameView) notValid = nameView.validate() || notValid;
            if (assignedView) notValid = assignedView.validate() || notValid;
            if (dateView) notValid = dateView.validate() || notValid;

            if (notValid) {
                Espo.Ui.error('Please fill in required fields.');
                return;
            }

            this.disableButton('save');

            this.taskModel.save().then(function () {
                Espo.Ui.success('Task created.');
                this.trigger('after:save', this.taskModel);
                this.close();
            }.bind(this)).catch(function () {
                this.enableButton('save');
            }.bind(this));
        }
    });
});
