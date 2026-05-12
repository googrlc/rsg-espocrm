define("custom:views/stream/panel-read-only", ["views/stream/panel"], function (Dep) {

    return Dep.extend({

        postDisabled: true,
        additionalEvents: {},

        setup: function () {
            this.postDisabled = true;
            Dep.prototype.setup.call(this);
        },

        data: function () {
            var data = Dep.prototype.data.call(this);

            data.postDisabled = true;

            return data;
        },

        enablePostingMode: function () {},

        initPostEvents: function () {},

        post: function () {
            return Promise.resolve();
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.$el.find(".post-container").addClass("hidden");
        },
    });
});
