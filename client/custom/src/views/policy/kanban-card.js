Espo.define('custom:views/policy/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-countdown',

        data: function () {
            const data = Dep.prototype.data.call(this);
            const countdown = this.buildCountdown(this.model.get('effectiveDate'), 'Until Active');

            return Object.assign({}, data, {
                cardSubtitle: this.model.get('carrier'),
                secondaryText: this.model.get('lineOfBusiness'),
                countdownClass: countdown.className,
                countdownLabel: countdown.label
            });
        },

        buildCountdown: function (dateValue, suffix) {
            if (!dateValue) {
                return { className: '', label: '' };
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const target = new Date(dateValue);
            target.setHours(0, 0, 0, 0);

            const diffDays = Math.floor((target - today) / 86400000);
            let className = 'countdown-upcoming';
            let label = '';

            if (diffDays < 0) {
                className = 'countdown-overdue';
                label = `${Math.abs(diffDays)} Days Active`;
            } else if (diffDays === 0) {
                className = 'countdown-today';
                label = 'Active Today';
            } else {
                if (diffDays <= 14) {
                    className = 'countdown-soon';
                }
                label = `${diffDays} Days ${suffix}`;
            }

            return { className, label };
        }
    });
});
