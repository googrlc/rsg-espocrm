define("custom:views/account/record/detail-view", ["crm:views/account/detail"], function (AccountDetailView) {

    return AccountDetailView.extend({

        setup: function () {
            AccountDetailView.prototype.setup.call(this);

            this.addMenuItem("buttons", {
                name: "createTask",
                label: "+ Task",
                action: "createTask",
                style: "warning",
                acl: "create",
                aclScope: "Task"
            });

            if (this.model.get("account_type") === "Prospect") {
                this.addRunIntelPackButton();
            }

            this.listenTo(this.model, "change:account_type", function () {
                if (this.model.get("account_type") === "Prospect") {
                    this.addRunIntelPackButton();
                } else {
                    this.removeMenuItem("runIntelPack");
                }
            }, this);
        },

        addRunIntelPackButton: function () {
            this.addMenuItem("buttons", {
                name: "runIntelPack",
                label: "Run Intel Pack",
                action: "runIntelPack",
                style: "info"
            });
        },

        actionCreateTask: function () {
            var accountId = this.model.get("id");
            var accountName = this.model.get("name");

            this.createView("taskModal", "custom:views/task/record/create-modal", {
                scope: "Task",
                sourceType: "Account",
                accountId: accountId,
                accountName: accountName,
                contextLabel: "Account-level task - " + accountName
            }, function (view) {
                view.render();
                this.listenToOnce(view, "after:save", function () {
                    var tasksPanel = this.getView("record") ? this.getView("record").getView("bottom") : null;
                    if (tasksPanel) {
                        var tasksPanelView = tasksPanel.getView("tasks");
                        if (tasksPanelView) {
                            tasksPanelView.actionRefresh();
                        }
                    }
                }, this);
            });
        },

        actionRunIntelPack: function () {
            var model = this.model;

            if (model.get("account_type") !== "Prospect") {
                Espo.Ui.warning("Intel Pack is only available for Prospect accounts.");
                return;
            }

            Espo.Ui.confirm("Run Intel Pack for " + model.get("name") + "?", {
                confirmText: "Run Intel Pack",
                cancelText: "Cancel"
            }, function () {
                Espo.Ui.notify("Running Intel Pack...");

                Espo.Ajax.postRequest("Account/action/runIntelPack", {
                    id: model.get("id")
                }).then(function () {
                    Espo.Ui.success("Intel Pack triggered successfully.");
                    model.fetch();
                }).catch(function () {
                    Espo.Ui.error("Intel Pack failed. Check webhook configuration.");
                });
            });
        }
    });
});
