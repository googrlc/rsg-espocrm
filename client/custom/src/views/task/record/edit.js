define('custom:views/task/record/edit', ['views/record/edit'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.applyEmailDefaults();
            Dep.prototype.setup.call(this);
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (!this.model.isNew() || !this.emailContextApplied) {
                return;
            }

            if (this.$el.find('.task-context-banner').length) {
                return;
            }

            this.$el.find('.record').first().prepend(
                '<div class="task-context-banner">Task drafted from email. Review the account, contact, priority, status, and notes before saving.</div>'
            );
        },

        applyEmailDefaults: function () {
            if (!this.model.isNew()) {
                return;
            }

            let defaults = null;

            try {
                defaults = JSON.parse(sessionStorage.getItem('rsgTaskEmailDefaults') || 'null');
                sessionStorage.removeItem('rsgTaskEmailDefaults');
            } catch (e) {
                defaults = null;
            }

            if (!defaults) {
                return;
            }

            this.emailContextApplied = true;
            this.model.set(defaults);
        }
    });
});
