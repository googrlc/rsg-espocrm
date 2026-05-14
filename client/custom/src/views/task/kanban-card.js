define('custom:views/task/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-task',

        data: function () {
            var data = Dep.prototype.data.call(this);
            var account = this.resolveAccount();
            var dueDate = this.buildDueDate(this.model.get('dateEnd') || this.model.get('dateEndDate'));
            var priority = this.buildPriority(this.model.get('priority'));
            var assignedUserName = this.model.get('assignedUserName') || 'Unassigned';
            var hasPriority = Boolean(priority.label);
            var hasDueDate = Boolean(dueDate.label);

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
            var id = this.model.get('accountId');
            var name = this.model.get('accountName');

            if (!id && this.model.get('parentType') === 'Account') {
                id = this.model.get('parentId');
                name = this.model.get('parentName');
            }

            return {id: id, name: name};
        },

        buildPriority: function (priority) {
            var label = priority || '';
            var normalized = String(priority || '').toLowerCase().replace(/\s+/g, '-');

            return {
                label: label,
                className: normalized ? 'task-priority-' + normalized : ''
            };
        },

        buildDueDate: function (dateValue) {
            if (!dateValue) {
                return {label: '', className: '', cardClass: ''};
            }

            var today = new Date();
            today.setHours(0, 0, 0, 0);

            var due = new Date(dateValue);
            due.setHours(0, 0, 0, 0);

            var diffDays = Math.floor((due - today) / 86400000);

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
                label: due.toLocaleDateString('en-US', {month: 'short', day: 'numeric'}),
                className: 'task-due-upcoming',
                cardClass: ''
            };
        },

        buildInitials: function (name) {
            var parts = String(name || '').trim().split(/\s+/).filter(Boolean);

            if (!parts.length || name === 'Unassigned') {
                return '--';
            }

            if (parts.length === 1) {
                return parts[0].substring(0, 2).toUpperCase();
            }

            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        },

        buildOwnerClass: function (name) {
            var normalizedName = String(name || '').toLowerCase();

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
