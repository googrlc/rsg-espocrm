/************************************************************************
 * RSG Account Score Sidebar Panel
 * Shows a simple live account value score driven by Total Active Premium.
 ************************************************************************/

define("custom:views/account/record/panels/account-score", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-account-score-panel">
                <div class="rsg-score-hero rsg-score-{{scoreStyle}}">
                    <div class="rsg-score-ring" style="--rsg-score-pct: {{scorePercent}}%;">
                        <div class="rsg-score-inner">
                            <div class="rsg-score-value">{{score}}</div>
                            <div class="rsg-score-label">Score</div>
                        </div>
                    </div>
                    <div class="rsg-score-summary">
                        <div class="rsg-score-tier">{{scoreTier}}</div>
                        <div class="rsg-score-premium">{{totalActivePremiumFormatted}}</div>
                        <div class="rsg-score-caption">Total Active Premium</div>
                    </div>
                </div>

                <div class="rsg-score-scale">
                    <div class="rsg-score-scale-row">
                        <span>Retention Priority</span>
                        <span class="label label-{{priorityStyle}}">{{priorityLabel}}</span>
                    </div>
                    <div class="rsg-score-bar">
                        <div class="rsg-score-bar-fill rsg-score-fill-{{scoreStyle}}" style="width: {{scorePercent}}%;"></div>
                    </div>
                    <div class="rsg-score-scale-labels">
                        <span>$0</span>
                        <span>$5k</span>
                        <span>$10k+</span>
                    </div>
                </div>

                <div class="rsg-score-facts">
                    <div class="rsg-score-fact">
                        <span class="rsg-score-fact-label">Active Policies</span>
                        <span class="rsg-score-fact-value">{{activePolicyCount}}</span>
                    </div>
                    <div class="rsg-score-fact">
                        <span class="rsg-score-fact-label">Next Renewal</span>
                        <span class="rsg-score-fact-value">{{nextRenewalDateFormatted}}</span>
                    </div>
                    <div class="rsg-score-fact">
                        <span class="rsg-score-fact-label">Days to Renewal</span>
                        <span class="rsg-score-fact-value">{{daysToRenewalFormatted}}</span>
                    </div>
                </div>
            </div>
            <style>
                .rsg-account-score-panel { padding: 2px 0; }
                .rsg-score-hero {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    border: 1px solid var(--border-color, #e6ebf2);
                    border-radius: 8px;
                    padding: 12px;
                    background: var(--panel-background-color, #fff);
                }
                .rsg-score-high { background: #f0fdf4; border-color: #bbf7d0; }
                .rsg-score-medium { background: #eff6ff; border-color: #bfdbfe; }
                .rsg-score-watch { background: #fffbeb; border-color: #fde68a; }
                .rsg-score-low { background: #f8fafc; border-color: #e2e8f0; }
                .rsg-score-ring {
                    width: 74px;
                    height: 74px;
                    border-radius: 50%;
                    display: grid;
                    place-items: center;
                    background:
                        conic-gradient(var(--rsg-score-color, #2563eb) var(--rsg-score-pct), #e5e7eb 0);
                    flex: 0 0 auto;
                }
                .rsg-score-high .rsg-score-ring { --rsg-score-color: #16a34a; }
                .rsg-score-medium .rsg-score-ring { --rsg-score-color: #2563eb; }
                .rsg-score-watch .rsg-score-ring { --rsg-score-color: #d97706; }
                .rsg-score-low .rsg-score-ring { --rsg-score-color: #64748b; }
                .rsg-score-inner {
                    width: 58px;
                    height: 58px;
                    border-radius: 50%;
                    background: var(--panel-background-color, #fff);
                    display: grid;
                    place-items: center;
                    align-content: center;
                    line-height: 1;
                }
                .rsg-score-value { font-size: 21px; font-weight: 700; color: var(--text-color, #1f2937); }
                .rsg-score-label,
                .rsg-score-caption,
                .rsg-score-fact-label,
                .rsg-score-scale-labels {
                    font-size: 10px;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    color: var(--text-muted-color, #6b7280);
                }
                .rsg-score-summary { min-width: 0; }
                .rsg-score-tier { font-size: 13px; font-weight: 700; color: var(--text-color, #1f2937); }
                .rsg-score-premium { margin-top: 3px; font-size: 18px; font-weight: 700; color: var(--primary-color, #2563eb); }
                .rsg-score-scale { margin-top: 10px; }
                .rsg-score-scale-row,
                .rsg-score-fact {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 8px;
                }
                .rsg-score-scale-row { margin-bottom: 6px; font-size: 12px; font-weight: 600; }
                .rsg-score-bar {
                    height: 8px;
                    border-radius: 999px;
                    background: #e5e7eb;
                    overflow: hidden;
                }
                .rsg-score-bar-fill { height: 100%; border-radius: inherit; }
                .rsg-score-fill-high { background: #16a34a; }
                .rsg-score-fill-medium { background: #2563eb; }
                .rsg-score-fill-watch { background: #d97706; }
                .rsg-score-fill-low { background: #64748b; }
                .rsg-score-scale-labels {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 4px;
                }
                .rsg-score-facts {
                    margin-top: 10px;
                    border-top: 1px solid var(--border-color, #e6ebf2);
                    padding-top: 6px;
                }
                .rsg-score-fact { padding: 4px 0; }
                .rsg-score-fact-value { font-size: 12px; font-weight: 600; text-align: right; }
            </style>
        `,

        data: function () {
            var totalActivePremium = this.asNumber(this.model.get("totalActivePremium"));
            var score = this.calculatePremiumScore(totalActivePremium);
            var activePolicyCount = this.model.get("activePolicyCount") || this.model.get("policyCountActive") || 0;
            var daysToRenewal = this.model.get("daysToRenewal");
            var scoreStyle = this.getScoreStyle(score);

            return {
                score: score,
                scorePercent: Math.max(0, Math.min(100, score)),
                scoreStyle: scoreStyle,
                scoreTier: this.getScoreTier(score),
                totalActivePremiumFormatted: this.formatCurrency(totalActivePremium),
                priorityLabel: this.getPriorityLabel(score, daysToRenewal),
                priorityStyle: this.getPriorityStyle(score, daysToRenewal),
                activePolicyCount: activePolicyCount,
                nextRenewalDateFormatted: this.formatDate(this.model.get("nextRenewalDate") || this.model.get("nextXDate")),
                daysToRenewalFormatted: this.formatDays(daysToRenewal)
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(
                this.model,
                "change:totalActivePremium change:activePolicyCount change:policyCountActive change:nextRenewalDate change:nextXDate change:daysToRenewal",
                this.reRender,
                this
            );
        },

        calculatePremiumScore: function (premium) {
            if (premium <= 0) return 0;
            if (premium >= 10000) return 100;

            return Math.round((premium / 10000) * 100);
        },

        getScoreTier: function (score) {
            if (score >= 80) return "Key Account";
            if (score >= 50) return "Growth Account";
            if (score >= 25) return "Watch Account";

            return "Light Account";
        },

        getScoreStyle: function (score) {
            if (score >= 80) return "high";
            if (score >= 50) return "medium";
            if (score >= 25) return "watch";

            return "low";
        },

        getPriorityLabel: function (score, daysToRenewal) {
            var days = this.asNumber(daysToRenewal);

            if (daysToRenewal !== null && daysToRenewal !== "" && days <= 30) {
                return "Critical";
            }

            if (score >= 80 || (daysToRenewal !== null && daysToRenewal !== "" && days <= 60)) {
                return "High";
            }

            if (score >= 50) {
                return "Medium";
            }

            return "Normal";
        },

        getPriorityStyle: function (score, daysToRenewal) {
            var label = this.getPriorityLabel(score, daysToRenewal);

            if (label === "Critical") return "danger";
            if (label === "High") return "warning";
            if (label === "Medium") return "info";

            return "default";
        },

        asNumber: function (value) {
            var number = parseFloat(value);

            return isNaN(number) ? 0 : number;
        },

        formatCurrency: function (value) {
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
                maximumFractionDigits: 0
            }).format(value || 0);
        },

        formatDate: function (value) {
            if (!value) return "\u2014";

            try {
                return new Date(value + "T00:00:00").toLocaleDateString("en-US", {
                    month: "short",
                    day: "numeric",
                    year: "numeric"
                });
            } catch (e) {
                return value;
            }
        },

        formatDays: function (value) {
            if (value === null || value === "" || typeof value === "undefined") return "\u2014";

            var days = this.asNumber(value);

            if (days < 0) return "Past due";
            if (days === 0) return "Today";
            if (days === 1) return "1 day";

            return days + " days";
        }
    });
});
