define("custom:controllers/stream", ["controllers/stream"], function (Dep) {

    return Dep.extend({

        actionIndex: function () {
            this.showReadOnlyStream(null);
        },

        actionPosts: function () {
            this.showReadOnlyStream("posts");
        },

        actionUpdates: function () {
            this.showReadOnlyStream("updates");
        },

        showReadOnlyStream: function (filter) {
            var key = "index";
            var isReturn = this.getRouter().backProcessed;

            if (!isReturn) {
                this.clearStoredMainView(key);
            }

            this.main("custom:views/stream/read-only", {
                displayTitle: true,
                filter: filter,
            }, undefined, {
                key: key,
                useStored: isReturn,
            });
        },
    });
});
