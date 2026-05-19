Espo.define('custom:views/core/kanban-card-countdown', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        /**
         * @param {string|null} dateValue
         * @param {{ suffix: string, overdueLabel: string, todayLabel: string }} options
         */
        buildCountdown: function (dateValue, options) {
            if (!dateValue) {
                return { className: '', label: '' };
            }

            options = options || {};

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const target = new Date(dateValue);
            target.setHours(0, 0, 0, 0);

            const diffDays = Math.floor((target - today) / 86400000);
            let className = 'countdown-upcoming';
            let label = '';

            if (diffDays < 0) {
                className = 'countdown-overdue';
                label = `${Math.abs(diffDays)} ${options.overdueLabel || 'Days Past'}`;
            } else if (diffDays === 0) {
                className = 'countdown-today';
                label = options.todayLabel || 'Today';
            } else {
                if (diffDays <= 14) {
                    className = 'countdown-soon';
                }
                label = `${diffDays} Days ${options.suffix || ''}`.trim();
            }

            return { className, label };
        }
    });
});
