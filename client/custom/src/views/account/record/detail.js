/************************************************************************
 * RSG Momentum Desk — Custom Account Record Detail View
 * Synchronizes middle-panel tabs with bottom-panel (relationship) visibility.
 *
 * Tab map (0-indexed, must match detail.json tabBreak order):
 *   0 = Overview          (stream)
 *   1 = Profile           (opportunities) — Business panels (CL/GB) OR Household panels (PL/Medicare/Life); hidden for Prospect/Carrier/MGA
 *   2 = Contacts          (contacts)
 *   3 = Policies          (policies, renewals)
 *   4 = Activity          (activityLogs, emails, meetings, calls, tasks)
 *   5 = Group Benefits    — conditional: GB only
 *   6 = Internal          (cases, commissions)
 ************************************************************************/

define("custom:views/account/record/detail", ["views/record/detail"], function (Dep) {

    return Dep.extend({

        // Map bottom panel names → middle tab index (must match detail.json)
        bottomPanelTabMap: {
            "stream":        0,
            "clientNotes":   0,
            "opportunities": 1,
            "contacts":      2,
            "policies":      3,
            "renewals":      3,
            "activityLogs":  4,
            "emails":        4,
            "meetings":      4,
            "calls":         4,
            "tasks":         4,
            "cases":         6,
            "commissions":   6,
        },

        // Tab indices for conditional visibility (must match detail.json tabBreak order)
        PROFILE_TAB: 1,
        GROUP_BENEFITS_TAB: 5,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.addButton({
                name: "createTask",
                label: "+ Task",
                style: "primary",
                acl: "create",
                aclScope: "Task",
                action: "createTask"
            });

            this.addButton({
                name: "createActivity",
                label: "+ Activity",
                style: "default",
                acl: "create",
                aclScope: "ActivityLog",
                action: "createActivity"
            });

            this.addButton({
                name: "createCrossSell",
                label: "Cross-Sell",
                style: "primary",
                acl: "create",
                aclScope: "Opportunity",
                action: "createCrossSell"
            });
        },

        actionCreateCrossSell: function () {
            var self = this;
            var accountName = this.model.get("name");

            this.createView("createOppModal", "views/modals/edit", {
                scope: "Opportunity",
                attributes: {
                    name: "Cross-Sell - " + accountName,
                    accountId: this.model.id,
                    accountName: accountName,
                    businessType: "Cross-Sell",
                    leadSource: "Existing Customer",
                    priority: "Warm",
                    stage: "Discovery"
                }
            }, function (view) {
                view.render();

                self.listenToOnce(view, "after:save", function () {
                    var bottomView = self.getView("bottom");

                    if (bottomView) {
                        var oppPanel = bottomView.getView("opportunities");

                        if (oppPanel) {
                            oppPanel.actionRefresh();
                        }
                    }
                });
            });
        },

        actionCreateTask: function () {
            var self = this;

            this.createView("createTaskModal", "custom:views/task/record/create-modal", {
                scope: "Task",
                sourceType: "Account",
                accountId: this.model.id,
                accountName: this.model.get("name"),
                parentType: "Account",
                parentId: this.model.id,
                parentName: this.model.get("name"),
                contextLabel: "Account-level task — " + this.model.get("name")
            }, function (view) {
                view.render();

                self.listenToOnce(view, "after:save", function () {
                    var bottomView = self.getView("bottom");

                    if (bottomView) {
                        var tasksPanel = bottomView.getView("tasks");

                        if (tasksPanel) {
                            tasksPanel.actionRefresh();
                        }
                    }
                });
            });
        },

        actionCreateActivity: function () {
            var self = this;

            this.createView("createActivityModal", "views/modals/edit", {
                scope: "ActivityLog",
                attributes: {
                    accountId: this.model.id,
                    accountName: this.model.get("name")
                }
            }, function (view) {
                view.render();

                self.listenToOnce(view, "after:save", function () {
                    var bottomView = self.getView("bottom");

                    if (bottomView) {
                        var activityPanel = bottomView.getView("activityLogs");

                        if (activityPanel) {
                            activityPanel.actionRefresh();
                        }
                    }
                });
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            var self = this;
            var tab = this.currentTab || 0;

            // Sync conditional tab visibility on render
            this.syncConditionalTabs();

            // Listen for account_type field changes (edit mode / inline edit)
            this.listenTo(this.model, "change:account_type", function () {
                self.syncConditionalTabs();
            });

            // Wait for bottom view to be ready, then sync panels
            var bottomView = this.getView("bottom");
            if (bottomView) {
                if (bottomView.isRendered()) {
                    self.syncBottomPanelsToTab(tab);
                } else {
                    this.listenToOnce(bottomView, "after:render", function () {
                        self.syncBottomPanelsToTab(tab);
                    });
                }
            }
        },

        selectTab: function (tab) {
            Dep.prototype.selectTab.call(this, tab);
            this.syncBottomPanelsToTab(tab);
        },

        /**
         * Show/hide the single "Profile" tab (slot 1) and the Group Benefits tab (slot 5)
         * based on account_type. The Profile tab holds BOTH the business panels (gated
         * CL/GB) and the household panels (gated PL/Medicare/Life) — only the matching set
         * renders via per-panel dynamicLogicVisible, so here we just show/hide the whole tab.
         *
         *   Commercial Lines / Group Benefits → Profile shows business panels
         *   Personal Lines / Medicare / Life Insurance → Profile shows household panels
         *   Group Benefits → also shows the dedicated Group Benefits tab
         *   Prospect / Carrier / MGA / (empty) → Profile hidden (no panels match)
         */
        syncConditionalTabs: function () {
            var type = this.model.get("account_type") || "";
            var $tabContainer = this.$el.find(".detail-button-container .nav-pills, .record > .middle > .tabs");

            if (!$tabContainer.length) return;

            var $allTabs = $tabContainer.find("> li");
            if (!$allTabs.length) return;

            var $profileTab = $allTabs.eq(this.PROFILE_TAB);
            var $gbTab = $allTabs.eq(this.GROUP_BENEFITS_TAB);

            var profileTypes = ["Commercial Lines", "Group Benefits", "Personal Lines", "Medicare", "Life Insurance"];

            if (profileTypes.indexOf(type) !== -1) {
                $profileTab.removeClass("tab-hidden");
            } else {
                $profileTab.addClass("tab-hidden");
            }

            if (type === "Group Benefits") {
                $gbTab.removeClass("tab-hidden");
            } else {
                $gbTab.addClass("tab-hidden");
            }

            // Hide customer-focused side panels for Carrier / MGA / Vendor
            this.syncSidePanelsForType(type);
        },

        syncSidePanelsForType: function (type) {
            var carrierTypes = ["Carrier", "MGA", "Vendor/Partner"];
            var isCarrier = carrierTypes.indexOf(type) !== -1;
            var hiddenSidePanels = ["commissionSummary", "nextcloudDocs", "documentLinks"];

            var sideView = this.getView("side");
            if (!sideView || !sideView.$el) return;

            hiddenSidePanels.forEach(function (name) {
                var $panel = sideView.$el.find('[data-name="' + name + '"]');
                if ($panel.length) {
                    if (isCarrier) {
                        $panel.addClass("hidden");
                    } else {
                        $panel.removeClass("hidden");
                    }
                }
            });
        },

        /**
         * Show only the bottom panels that belong to the currently active tab.
         * Panels not assigned to any tab (assignedTab === -1) are always visible.
         */
        syncBottomPanelsToTab: function (tab) {
            var bottomView = this.getView("bottom");
            if (!bottomView || !bottomView.$el) return;

            var map = this.bottomPanelTabMap;

            (bottomView.panelList || []).forEach(function (panel) {
                if (!panel.name || panel.name === "_delimiter_") return;

                var assignedTab = (panel.name in map) ? map[panel.name] : -1;

                // -1 means show on all tabs
                if (assignedTab === -1) return;

                var $panel = bottomView.$el.find(".panel[data-name=\"" + panel.name + "\"]");

                if (assignedTab === tab) {
                    $panel.removeClass("hidden");
                    panel.hidden = false;
                    panel.tabHidden = false;
                } else {
                    $panel.addClass("hidden");
                    panel.tabHidden = true;
                }
            });
        },
    });
});
