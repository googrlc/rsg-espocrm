define('custom:views/email/record/detail', ['views/email/record/detail'], function (Dep) {

    return Dep.extend({

        events: _.extend({}, Dep.prototype.events || {}, {
            'click [data-action="createTaskFromEmail"]': function (e) {
                e.preventDefault();
                this.actionCreateTaskFromEmail();
            },
            'click [data-action="createContactFromEmail"]': function (e) {
                e.preventDefault();
                this.actionCreateContactFromEmail();
            }
        }),

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.addCreateTaskButton();

            // Wrapped so a failure here can never block the email from rendering.
            try {
                this.addCreateContactButton();
            } catch (e) {
                console.error('createContactFromEmail button failed', e);
            }
        },

        addCreateTaskButton: function () {
            if (this.$el.find('[data-action="createTaskFromEmail"]').length) {
                return;
            }

            const $container = this.$el.find('.detail-button-container, .button-container').first();

            if (!$container.length) {
                return;
            }

            $container.append(
                '<button type="button" class="btn btn-default btn-sm" data-action="createTaskFromEmail">' +
                    '<span class="fas fa-tasks"></span> Create Task' +
                '</button>'
            );
        },

        addCreateContactButton: function () {
            if (this.$el.find('[data-action="createContactFromEmail"]').length) {
                return;
            }

            if (!this.getAcl().checkScope('Contact', 'create')) {
                return;
            }

            const $container = this.$el.find('.detail-button-container, .button-container').first();

            if (!$container.length) {
                return;
            }

            $container.append(
                '<button type="button" class="btn btn-default btn-sm" data-action="createContactFromEmail">' +
                    '<span class="fas fa-user-plus"></span> Create Contact' +
                '</button>'
            );
        },

        actionCreateContactFromEmail: function () {
            const from = this.parseFrom();
            const name = this.splitName(from.name);

            Espo.Ui.notify(this.translate('pleaseWait', 'messages'));

            this.createView('quickCreate', 'views/modals/edit', {
                scope: 'Contact',
                attributes: {
                    emailAddress: from.address,
                    firstName: name.first,
                    lastName: name.last
                }
            }, function (view) {
                view.render();
                Espo.Ui.notify(false);

                this.listenToOnce(view, 'after:save', function (model) {
                    Espo.Ui.success(this.translate('Created'));
                    this.linkEmailToContact(model);
                }, this);
            }.bind(this));
        },

        linkEmailToContact: function (model) {
            // Only set the email's parent if it has none — never clobber an existing link.
            if (this.model.get('parentId')) {
                return;
            }

            this.model.save({
                parentType: 'Contact',
                parentId: model.id,
                parentName: model.get('name')
            }, { patch: true });
        },

        parseFrom: function () {
            const address = (this.model.get('from') || '').trim();
            let name = '';

            const nameHash = this.model.get('nameHash') || {};

            if (address && nameHash[address]) {
                name = nameHash[address];
            }

            if (!name) {
                const fromString = this.model.get('fromString') || '';
                const match = fromString.match(/^\s*"?([^"<]*?)"?\s*(?:<[^>]*>)?\s*$/);

                if (match && match[1] && match[1].indexOf('@') === -1) {
                    name = match[1].trim();
                }
            }

            return { address: address, name: name };
        },

        splitName: function (name) {
            name = (name || '').trim();

            if (!name) {
                return { first: '', last: '' };
            }

            const parts = name.split(/\s+/);

            if (parts.length === 1) {
                return { first: '', last: parts[0] };
            }

            const first = parts.shift();

            return { first: first, last: parts.join(' ') };
        },

        actionCreateTaskFromEmail: function () {
            const defaults = this.buildTaskDefaults();

            try {
                sessionStorage.setItem('rsgTaskEmailDefaults', JSON.stringify(defaults));
            } catch (e) {}

            this.getRouter().navigate('#Task/create', { trigger: true });
        },

        buildTaskDefaults: function () {
            const subject = this.model.get('subject') || this.model.get('name') || 'Email follow-up';
            const account = this.resolveAccount();
            const contact = this.resolveContact();

            const defaults = {
                name: subject,
                status: 'Inbox',
                priority: this.suggestPriority(subject),
                taskType: this.suggestTaskType(subject),
                taskSource: account.id ? 'Account' : (contact.id ? 'Contact' : ''),
                syncSource: 'Gmail',
                description: this.buildDescription(subject)
            };

            if (account.id) {
                defaults.accountId = account.id;
                defaults.accountName = account.name;
            }

            if (contact.id) {
                defaults.contactId = contact.id;
                defaults.contactName = contact.name;
            }

            if (this.model.get('parentId') && this.model.get('parentType')) {
                defaults.parentId = this.model.get('parentId');
                defaults.parentName = this.model.get('parentName');
                defaults.parentType = this.model.get('parentType');
            } else if (account.id) {
                defaults.parentId = account.id;
                defaults.parentName = account.name;
                defaults.parentType = 'Account';
            } else if (contact.id) {
                defaults.parentId = contact.id;
                defaults.parentName = contact.name;
                defaults.parentType = 'Contact';
            }

            return defaults;
        },

        resolveAccount: function () {
            if (this.model.get('accountId')) {
                return {
                    id: this.model.get('accountId'),
                    name: this.model.get('accountName')
                };
            }

            if (this.model.get('parentType') === 'Account') {
                return {
                    id: this.model.get('parentId'),
                    name: this.model.get('parentName')
                };
            }

            return { id: null, name: null };
        },

        resolveContact: function () {
            if (this.model.get('parentType') === 'Contact') {
                return {
                    id: this.model.get('parentId'),
                    name: this.model.get('parentName')
                };
            }

            return { id: null, name: null };
        },

        suggestPriority: function (subject) {
            const text = String(subject || '').toLowerCase();

            if (/(cancel|non[- ]?renew|claim|urgent|asap|deadline|late|overdue)/.test(text)) {
                return 'Urgent';
            }

            if (/(renew|quote|change|endorse|billing|payment|follow up|follow-up)/.test(text)) {
                return 'High';
            }

            return 'Normal';
        },

        suggestTaskType: function (subject) {
            const text = String(subject || '').toLowerCase();

            if (/renew/.test(text)) {
                return 'Renewal';
            }

            if (/(quote|new business|proposal)/.test(text)) {
                return 'New Business';
            }

            if (/(change|endorse|coverage|vehicle|driver)/.test(text)) {
                return 'Policy Change';
            }

            if (/claim/.test(text)) {
                return 'Claims';
            }

            return 'Follow Up';
        },

        buildDescription: function (subject) {
            const lines = [
                'Email context',
                'Subject: ' + subject
            ];

            if (this.model.get('fromString')) {
                lines.push('From: ' + this.model.get('fromString'));
            }

            if (this.model.get('toString')) {
                lines.push('To: ' + this.model.get('toString'));
            }

            if (this.model.get('dateSent')) {
                lines.push('Date: ' + this.model.get('dateSent'));
            }

            lines.push('Email record: #Email/view/' + this.model.id);

            const body = this.stripHtml(this.model.get('body') || '').trim();

            if (body) {
                lines.push('');
                lines.push(body.substring(0, 1200));
            }

            return lines.join('\n');
        },

        stripHtml: function (value) {
            return String(value)
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/p>/gi, '\n')
                .replace(/<[^>]+>/g, ' ')
                .replace(/\s+\n/g, '\n')
                .replace(/[ \t]{2,}/g, ' ');
        }
    });
});
