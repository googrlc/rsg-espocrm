Espo.define('custom:views/lead/kanban-card', ['custom:views/core/kanban-card'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card',

        data: function () {
            const data = Dep.prototype.data.call(this);
            const stagnationDays = this.buildStagnationDays();
            const { priorityClass, priorityLabel } = this.buildPriority();
            const ownerInitials = this.buildOwnerInitials();
            const estimatedPremium = this.model.get('estimatedPremium');
            const formattedValue = this.buildFormattedValue('estimatedPremium', 'estimatedPremiumCurrency');
            const { cardClass, stagnationClass } = this.buildCardClasses(estimatedPremium, stagnationDays);

            // Countdown label for T65/new lead timing.
            const countdownDate = this.model.get('targetDate') || this.model.get('xDate');
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

            return Object.assign({}, data, {
                priorityClass: priorityClass,
                priorityLabel: priorityLabel,
                dueDateClass: dueDateClass,
                dueDateLabel: dueDateLabel,
                ownerInitials: ownerInitials,
                assignedUserName: this.model.get('assignedUserName'),
                source: this.model.get('source'),
                insuranceInterest: this.model.get('insuranceInterest'),
                formattedValue: formattedValue,
                cardClass: cardClass,
                stagnationDays: stagnationDays,
                stagnationClass: stagnationClass
            });
        }
    });
});
