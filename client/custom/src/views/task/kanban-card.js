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
            const status = this.buildStatus(this.model.get('status'));
            const assignedUserName = this.model.get('assignedUserName') || 'Unassigned';

            return Object.assign({}, data, {
                accountId: account.id,
                accountName: account.name,
                statusLabel: status.label,
                statusClass: status.className,
                priorityLabel: priority.label,
                priorityClass: priority.className,
                dueDateLabel: dueDate.label,
                dueDateClass: dueDate.className,
                taskType: this.model.get('taskType'),
                assignedUserName: assignedUserName,
                ownerInitials: this.buildInitials(assignedUserName),
                cardClass: dueDate.cardClass
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

        buildStatus: function (status) {
            const label = status || '';
            const normalized = String(status || '').toLowerCase().replace(/\s+/g, '-');

            return {
                label: label,
                className: normalized ? 'task-status-' + normalized : ''
            };
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
                    label: Math.abs(diffDays) + 'd overdue',
                    className: 'task-due-overdue',
                    cardClass: 'task-card-overdue'
                };
            }

            if (diffDays === 0) {
                return {
                    label: 'Due today',
                    className: 'task-due-today',
                    cardClass: 'task-card-due-today'
                };
            }

            if (diffDays === 1) {
                return { label: 'Due tomorrow', className: 'task-due-soon', cardClass: '' };
            }

            if (diffDays <= 7) {
                return { label: 'Due in ' + diffDays + 'd', className: 'task-due-soon', cardClass: '' };
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
