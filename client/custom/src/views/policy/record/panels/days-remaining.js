define("custom:views/policy/record/panels/days-remaining", ["view"], function (Dep) {
    return Dep.extend({
        templateContent: `
            <div class="rsg-days-panel">
                <div class="rsg-days-badge rsg-days-{{badgeClass}}">
                    <div class="rsg-days-value">{{daysValue}}</div>
                    <div class="rsg-days-label">{{statusLabel}}</div>
                </div>
            </div>
            <style>
                .rsg-days-badge {
                    border-radius: 12px;
                    padding: 14px 12px;
                    text-align: center;
                    border: 1px solid rgba(0, 0, 0, 0.08);
                }
                .rsg-days-value {
                    font-size: 28px;
                    font-weight: 700;
                    line-height: 1;
                    margin-bottom: 6px;
                }
                .rsg-days-label {
                    font-size: 12px;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                }
                .rsg-days-good { background: #eef8f0; color: #216e39; }
                .rsg-days-watch { background: #eef5fb; color: #24557a; }
                .rsg-days-warn { background: #fff4e5; color: #9a5b00; }
                .rsg-days-danger { background: #fdeceb; color: #9f2d2d; }
            </style>
        `,

        data: function () {
            var days = this.model.get("daysRemaining");
            var statusLabel = this.model.get("statusLabel") || "ACTIVE";
            var badgeClass = "good";

            if (days === null || typeof days === "undefined") {
                days = "--";
            } else if (days <= 0) {
                badgeClass = "danger";
            } else if (days <= 30) {
                badgeClass = "warn";
            } else if (days <= 60) {
                badgeClass = "watch";
            }

            return {
                daysValue: days,
                statusLabel: statusLabel,
                badgeClass: badgeClass
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:daysRemaining change:statusLabel", this.reRender, this);
        }
    });
});
