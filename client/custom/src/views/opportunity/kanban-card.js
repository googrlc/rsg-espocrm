Espo.define('custom:views/opportunity/kanban-card', ['custom:views/core/kanban-card'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-opportunity',

        data: function () {
            const data = Dep.prototype.data.call(this);
            const stagnationDays = this.buildStagnationDays();
            const { priorityClass, priorityLabel } = this.buildPriority();
            const ownerInitials = this.buildOwnerInitials();
            const estimatedPremium = this.model.get('estimatedPremium');
            const formattedValue = this.buildFormattedValue('estimatedPremium', 'estimatedPremiumCurrency');
            const { cardClass, stagnationClass } = this.buildCardClasses(estimatedPremium, stagnationDays);

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

            return Object.assign({}, data, {
                priorityClass: priorityClass,
                priorityLabel: priorityLabel,
                dueDateClass: dueDateClass,
                dueDateLabel: dueDateLabel,
                ownerInitials: ownerInitials,
                assignedUserName: this.model.get('assignedUserName'),
                lineOfBusiness: this.model.get('lineOfBusiness'),
                businessType: this.model.get('businessType'),
                formattedValue: formattedValue,
                accountName: this.model.get('accountName') || this.model.get('account'),
                cardClass: cardClass,
                stagnationDays: stagnationDays,
                stagnationClass: stagnationClass
            });
        }
    });
});
