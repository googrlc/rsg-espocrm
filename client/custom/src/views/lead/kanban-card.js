/**
 * Enhanced Kanban Card View for Lead Entity
 * Adds: priority badges, contact info, deal value, strategic tags,
 * stagnation timer, activity counter, and checklist progress.
 */
Espo.define('custom:views/lead/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card',

        data: function () {
            const data = Dep.prototype.data.call(this);

            // Calculate stagnation days
            const modifiedAt = this.model.get('modifiedAt');
            let stagnationDays = 0;
            if (modifiedAt) {
                const now = new Date();
                const modified = new Date(modifiedAt);
                stagnationDays = Math.floor((now - modified) / (1000 * 60 * 60 * 24));
            }

            // Priority styling
            const priority = this.model.get('priority');
            let priorityClass = '';
            let priorityLabel = '';
            if (priority === 'Hot') {
                priorityClass = 'priority-hot';
                priorityLabel = 'HOT';
            } else if (priority === 'Warm') {
                priorityClass = 'priority-warm';
                priorityLabel = 'WARM';
            } else if (priority === 'Cold') {
                priorityClass = 'priority-cold';
                priorityLabel = 'COLD';
            }

            // Due date color coding
            const xDate = this.model.get('xDate');
            let dueDateClass = 'due-date-upcoming';
            let dueDateLabel = '';
            if (xDate) {
                const now = new Date();
                const due = new Date(xDate);
                const diffDays = Math.floor((due - now) / (1000 * 60 * 60 * 24));
                
                if (diffDays < 0) {
                    dueDateClass = 'due-date-overdue';
                    dueDateLabel = `Overdue (${Math.abs(diffDays)}d)`;
                } else if (diffDays === 0) {
                    dueDateClass = 'due-date-today';
                    dueDateLabel = 'Today';
                } else if (diffDays <= 7) {
                    dueDateLabel = `${diffDays}d`;
                } else {
                    dueDateLabel = due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }

            // Owner avatar initials
            const assignedUser = this.model.get('assignedUser');
            let ownerInitials = '??';
            if (assignedUser) {
                const nameParts = assignedUser.split(' ');
                if (nameParts.length >= 2) {
                    ownerInitials = (nameParts[0][0] + nameParts[nameParts.length - 1][0]).toUpperCase();
                } else {
                    ownerInitials = assignedUser.substring(0, 2).toUpperCase();
                }
            }

            // Lead source tag
            const source = this.model.get('source');
            
            // Product category tag
            const insuranceInterest = this.model.get('insuranceInterest');

            // Deal value
            const estimatedPremium = this.model.get('estimatedPremium');
            let formattedValue = '';
            if (estimatedPremium) {
                formattedValue = this.getHelper().formatCurrency(estimatedPremium, 'USD');
            }

            // High value card conditional formatting
            let cardClass = '';
            if (estimatedPremium && estimatedPremium >= 5000) {
                cardClass = 'high-value';
            }

            // Stagnation conditional formatting
            if (stagnationDays > 14) {
                cardClass += ' critical-stagnant';
            } else if (stagnationDays > 7) {
                cardClass += ' stagnant';
            }

            // Stagnation timer styling
            let stagnationClass = '';
            if (stagnationDays > 14) {
                stagnationClass = 'stagnation-critical';
            } else if (stagnationDays > 7) {
                stagnationClass = 'stagnation-warning';
            }

            return Object.assign({}, data, {
                priorityClass: priorityClass,
                priorityLabel: priorityLabel,
                dueDateClass: dueDateClass,
                dueDateLabel: dueDateLabel,
                ownerInitials: ownerInitials,
                source: source,
                insuranceInterest: insuranceInterest,
                formattedValue: formattedValue,
                cardClass: cardClass,
                stagnationDays: stagnationDays,
                stagnationClass: stagnationClass
            });
        },

        getActionMap: function () {
            return {
                'select': this.model.id
            };
        }
    });
});
