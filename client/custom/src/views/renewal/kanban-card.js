Espo.define('custom:views/renewal/kanban-card', ['custom:views/core/kanban-card-countdown'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-countdown',

        data: function () {
            const data = Dep.prototype.data.call(this);
            const countdown = this.buildCountdown(this.model.get('expiration_date'), {
                suffix: 'Until Renewal',
                overdueLabel: 'Days Past Renewal',
                todayLabel: 'Renewal Today'
            });

            return Object.assign({}, data, {
                cardSubtitle: this.model.get('accountName') || this.model.get('carrier'),
                secondaryText: this.model.get('line_of_business'),
                countdownClass: countdown.className,
                countdownLabel: countdown.label
            });
        }
    });
});
