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

            // Countdown label for T65/new lead timing.
            const countdownDate = this.model.get('targetDate') || this.model.get('x_date');
            let dueDateClass = 'due-date-upcoming';
            let dueDateLabel = '';
            if (countdownDate) {
                const now = new Date();
                const due = new Date(countdownDate);
                const diffDays = Math.floor((due - now) / (1000 * 60 * 60 * 24));
                
                if (diffDays < 0) {
                    dueDateClass = 'due-date-overdue';
                    dueDateLabel = `${Math.abs(diffDays)} Days Past Target`;
                } else if (diffDays === 0) {
                    dueDateClass = 'due-date-today';
                    dueDateLabel = 'Today';
                } else {
                    dueDateLabel = `${diffDays} Days Remaining`;
                }
            }

            // Owner avatar initials
            const assignedUserName = this.model.get('assignedUserName');
            let ownerInitials = '??';
            if (assignedUserName) {
                const nameParts = assignedUserName.trim().split(/\s+/).filter(Boolean);
                if (nameParts.length >= 2) {
                    ownerInitials = (nameParts[0][0] + nameParts[nameParts.length - 1][0]).toUpperCase();
                } else if (nameParts.length === 1) {
                    ownerInitials = nameParts[0].substring(0, 2).toUpperCase();
                }
            }

            // Lead source tag
            const source = this.model.get('source');
            
            // Product category tag
            const insuranceInterest = this.model.get('insurance_interest');

            // Deal value
            const estimatedPremium = this.model.get('estimated_premium');
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
                assignedUserName: assignedUserName,
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
