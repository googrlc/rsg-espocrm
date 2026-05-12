Espo.define('custom:views/task/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-task',

        events: _.extend({}, Dep.prototype.events || {}, {
            'click .task-card-actions': function (e) {
                e.stopPropagation();
            },
            'click a': function (e) {
                e.stopPropagation();
            }
        }),

        data: function () {
            const data = Dep.prototype.data.call(this);
            const account = this.resolveAccount();
            const dueDate = this.buildDueDate(this.model.get('dateEnd') || this.model.get('dateEndDate'));
            const priority = this.buildPriority(this.model.get('priority'));
            const assignedUserName = this.model.get('assignedUserName') || 'Unassigned';
            const hasPriority = Boolean(priority.label);
            const hasDueDate = Boolean(dueDate.label);

            return Object.assign({}, data, {
                accountId: account.id,
                accountName: account.name,
                priorityLabel: priority.label,
                priorityClass: priority.className,
                dueDateLabel: dueDate.label,
                dueDateClass: dueDate.className,
                hasMetaLine: hasPriority || hasDueDate,
                showMetaSeparator: hasPriority && hasDueDate,
                assignedUserName: assignedUserName,
                ownerInitials: this.buildInitials(assignedUserName),
                cardClass: dueDate.cardClass,
                ownerClass: this.buildOwnerClass(assignedUserName)
            });
        },

        resolveAccount: function () {
            let id = this.model.get('linkedAccountId') || this.model.get('accountId');
            let name = this.model.get('linkedAccountName') || this.model.get('accountName');

            if (!id && this.model.get('parentType') === 'Account') {
                id = this.model.get('parentId');
                name = this.model.get('parentName');
            }

            return { id: id, name: name };
        },

        buildPriority: function (priority) {
            const label = priority || '';
            const normalized = String(priority || '').toLowerCase().replace(/\s+/g, '-');

            return {
                label: label,
                className: normalized ? 'task-priority-' + normalized : ''
            };
        },

        buildDueDate: function (dateValue) {
            if (!dateValue) {
                return { label: '', className: '', cardClass: '' };
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const due = new Date(dateValue);
            due.setHours(0, 0, 0, 0);

            const diffDays = Math.floor((due - today) / 86400000);

            if (diffDays < 0) {
                return {
                    label: 'Overdue',
                    className: 'task-due-overdue',
                    cardClass: 'task-card-overdue'
                };
            }

            if (diffDays === 0) {
                return {
                    label: 'Today',
                    className: 'task-due-today',
                    cardClass: 'task-card-due-today'
                };
            }

            return {
                label: due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
                className: 'task-due-upcoming',
                cardClass: ''
            };
        },

        buildInitials: function (name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);

            if (!parts.length || name === 'Unassigned') {
                return '--';
            }

            if (parts.length === 1) {
                return parts[0].substring(0, 2).toUpperCase();
            }

            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },

        buildOwnerClass: function (name) {
            const normalizedName = String(name || '').toLowerCase();

            if (normalizedName.includes('gretch')) {
                return 'task-owner-gretchen';
            }

            if (normalizedName.includes('lamar')) {
                return 'task-owner-lamar';
            }

            return 'task-owner-default';
        },

        actionQuickRemove: function () {
            if (!confirm('Delete this task?')) {
                return;
            }

            this.model.destroy({
                wait: true,
                success: function () {
                    this.remove();
                }.bind(this)
            });
        }
    });
});
