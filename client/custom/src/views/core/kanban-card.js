Espo.define('custom:views/core/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        buildStagnationDays: function () {
            const modifiedAt = this.model.get('modifiedAt');
            if (!modifiedAt) {
                return 0;
            }
            const now = new Date();
            const modified = new Date(modifiedAt);
            return Math.floor((now - modified) / (1000 * 60 * 60 * 24));
        },

        buildPriority: function () {
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
            return { priorityClass, priorityLabel };
        },

        buildOwnerInitials: function () {
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
            return ownerInitials;
        },

        buildFormattedValue: function (fieldName, currencyField) {
            const value = this.model.get(fieldName);
            const currency = this.model.get(currencyField) || 'USD';
            if (value || value === 0) {
                return this.getHelper().formatCurrency(value, currency);
            }
            return '';
        },

        buildCardClasses: function (estimatedPremium, stagnationDays) {
            let cardClass = '';
            if (estimatedPremium && estimatedPremium >= 5000) {
                cardClass = 'high-value';
            }
            if (stagnationDays > 14) {
                cardClass += ' critical-stagnant';
            } else if (stagnationDays > 7) {
                cardClass += ' stagnant';
            }

            let stagnationClass = '';
            if (stagnationDays > 14) {
                stagnationClass = 'stagnation-critical';
            } else if (stagnationDays > 7) {
                stagnationClass = 'stagnation-warning';
            }

            return { cardClass: cardClass.trim(), stagnationClass };
        },

        getActionMap: function () {
            return {
                'select': this.model.id
            };
        }
    });
});
