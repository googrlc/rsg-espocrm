/**
 * Enhanced Kanban Card View for Opportunity Entity
 * Adds: account name, deal value, priority, business type, stage progress
 */
Espo.define('custom:views/opportunity/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-opportunity',

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

            // Close date color coding
            const closeDate = this.model.get('closeDate');
            let dueDateClass = 'due-date-upcoming';
            let dueDateLabel = '';
            if (closeDate) {
                const now = new Date();
                const due = new Date(closeDate);
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

            // Deal value
            const estimatedPremium = this.model.get('estimated_premium');
            const estimatedPremiumCurrency = this.model.get('estimated_premiumCurrency') || 'USD';
            let formattedValue = '';
            if (estimatedPremium || estimatedPremium === 0) {
                formattedValue = this.getHelper().formatCurrency(estimatedPremium, estimatedPremiumCurrency);
            }

            // Account name
            const account = this.model.get('account');
            const accountName = this.model.get('accountName') || account;

            // Line of Business
            const lineOfBusiness = this.model.get('line_of_business');
            
            // Business Type
            const businessType = this.model.get('business_type');

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
                lineOfBusiness: lineOfBusiness,
                businessType: businessType,
                formattedValue: formattedValue,
                accountName: accountName,
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
