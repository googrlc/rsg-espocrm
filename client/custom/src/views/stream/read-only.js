define("custom:views/stream/read-only", ["views/stream"], function (Dep) {

    return Dep.extend({

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.$el.find('[data-action="createPost"]').addClass("hidden").attr("disabled", "disabled");
        },

        actionCreatePost: function () {
            Espo.Ui.notify("Native stream posting is read-only. Use Client Notes.", "warning", 3000);
        },
    });
});
