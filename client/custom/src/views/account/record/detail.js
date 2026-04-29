/************************************************************************
 * RSG Momentum Desk — Custom Account Record Detail View
 * Synchronizes middle-panel tabs with bottom-panel (relationship) visibility.
 *
 * Tab map (0-indexed, must match detail.json tabBreak order):
 *   0 = Overview          (stream)
 *   1 = Contacts          (contacts)
 *   2 = Policies          (policies, renewals)
 *   3 = Activity          (activityLogs, emails, meetings, calls, tasks)
 *   4 = Business Profile  (opportunities) — conditional: CL/GB only
 *   5 = Group Benefits    — conditional: GB only
 *   6 = Internal          (cases, commissions)
 ************************************************************************/

define("custom:views/account/record/detail", ["views/record/detail"], function (Dep) {

    return Dep.extend({

        // Map bottom panel names → middle tab index (must match detail.json)
        bottomPanelTabMap: {
            "stream":        0,
            "contacts":      1,
            "policies":      2,
            "renewals":      2,
            "activityLogs":  3,
            "emails":        3,
            "meetings":      3,
            "calls":         3,
            "tasks":         3,
            "opportunities": 4,
            "cases":         6,
            "commissions":   6,
        },

        // Tab indices for conditional visibility
        BUSINESS_PROFILE_TAB: 4,
        GROUP_BENEFITS_TAB: 5,

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
         * Hide/show Business Profile (tab 4) and Group Benefits (tab 5)
         * based on account_type field value.
         *
         * Commercial Lines → show Business Profile, hide Group Benefits
         * Group Benefits → show both Business Profile and Group Benefits
         * Personal Lines → show Business Profile (PL panels use dynamicLogicVisible)
         * All others (Prospect, etc.) → hide both
         */
        syncConditionalTabs: function () {
            var type = this.model.get("account_type") || "";
            var $tabContainer = this.$el.find(".detail-button-container .nav-pills, .record > .middle > .tabs");

            if (!$tabContainer.length) return;

            var $allTabs = $tabContainer.find("> li");
            if (!$allTabs.length) return;

            var $businessTab = $allTabs.eq(this.BUSINESS_PROFILE_TAB);
            var $gbTab = $allTabs.eq(this.GROUP_BENEFITS_TAB);

            if (type === "Commercial Lines") {
                $businessTab.removeClass("tab-hidden");
                $gbTab.addClass("tab-hidden");
            } else if (type === "Group Benefits") {
                $businessTab.removeClass("tab-hidden");
                $gbTab.removeClass("tab-hidden");
            } else if (type === "Personal Lines") {
                $businessTab.removeClass("tab-hidden");
                $gbTab.addClass("tab-hidden");
            } else {
                $businessTab.addClass("tab-hidden");
                $gbTab.addClass("tab-hidden");
            }
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
