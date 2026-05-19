Espo.define('custom:views/policy/kanban-card', ['custom:views/core/kanban-card-countdown'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-countdown',

        data: function () {
            const data = Dep.prototype.data.call(this);
            const countdown = this.buildCountdown(this.model.get('effective_date'), {
                suffix: 'Until Active',
                overdueLabel: 'Days Active',
                todayLabel: 'Active Today'
            });

            return Object.assign({}, data, {
                cardSubtitle: this.model.get('carrier'),
                secondaryText: this.model.get('line_of_business'),
                countdownClass: countdown.className,
                countdownLabel: countdown.label
            });
        }
    });
});
