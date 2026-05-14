define('custom:views/email/record/detail', ['views/email/record/detail'], function (Dep) {

    return Dep.extend({

        events: _.extend({}, Dep.prototype.events || {}, {
            'click [data-action="createTaskFromEmail"]': function (e) {
                e.preventDefault();
                this.actionCreateTaskFromEmail();
            }
        }),

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.addCreateTaskButton();
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
