/************************************************************************
 * RSG Commission Summary Sidebar Panel
 * Gives Accounts a live commission rollup without widening the Account table.
 ************************************************************************/

define("custom:views/account/record/panels/commission-summary", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-commission-panel">
                {{#if isLoading}}
                    <div class="rsg-commission-state text-muted">Loading ledger summary…</div>
                {{else}}
                    {{#if loadFailed}}
                        <div class="rsg-commission-state text-danger">Couldn’t load commission summary.</div>
                    {{else}}
                        <div class="rsg-commission-hero">
                            <div class="rsg-commission-hero-value">{{earnedAmountFormatted}}</div>
                            <div class="rsg-commission-hero-label">Posted Commission</div>
                        </div>

                        <div class="rsg-commission-grid">
                            <div class="rsg-commission-card">
                                <div class="rsg-commission-card-value">{{pipelineAmountFormatted}}</div>
                                <div class="rsg-commission-card-label">Open Pipeline</div>
                            </div>
                            <div class="rsg-commission-card {{#if hasOverdue}}rsg-commission-card-danger{{/if}}">
                                <div class="rsg-commission-card-value">{{overdueAmountFormatted}}</div>
                                <div class="rsg-commission-card-label">Overdue</div>
                            </div>
                        </div>

                        <div class="rsg-commission-stats">
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Records</span>
                                <span class="rsg-commission-value">{{count}}</span>
                            </div>
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Estimated</span>
                                <span class="rsg-commission-value">{{estimatedCount}}</span>
                            </div>
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Posted</span>
                                <span class="rsg-commission-value">{{postedCount}}</span>
                            </div>
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Overdue</span>
                                <span class="rsg-commission-value">{{overdueCount}}</span>
                            </div>
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Last Paid</span>
                                <span class="rsg-commission-value">{{lastPaymentDateFormatted}}</span>
                            </div>
                            <div class="rsg-commission-row">
                                <span class="rsg-commission-label">Next Expected</span>
                                <span class="rsg-commission-value">{{nextExpectedDateFormatted}}</span>
                            </div>
                        </div>

                        {{#if latestCommissions.length}}
                        <div class="rsg-commission-subtitle">Recent Ledger</div>
                        <div class="rsg-commission-list">
                            {{#each latestCommissions}}
                            <div class="rsg-commission-item">
                                <div class="rsg-commission-item-main">
                                    <div class="rsg-commission-item-name">{{name}}</div>
                                    <div class="rsg-commission-item-date">{{displayDateFormatted}}</div>
                                </div>
                                <div class="rsg-commission-item-side">
                                    <span class="label label-{{statusStyle}}">{{status}}</span>
                                    <div class="rsg-commission-item-amount">{{amountFormatted}}</div>
                                </div>
                            </div>
                            {{/each}}
                        </div>
                        {{/if}}

                        <div class="rsg-commission-actions">
                            <a href="javascript:" data-action="refreshSummary" class="text-muted">refresh</a>
                        </div>
                    {{/if}}
                {{/if}}
            </div>
            <style>
                .rsg-commission-panel { padding: 2px 0; }
                .rsg-commission-state { padding: 6px 0; font-size: 12px; }
                .rsg-commission-hero {
                    border: 1px solid var(--border-color, #e6ebf2);
                    background: linear-gradient(135deg, #f7fbff 0%, #eef5ff 100%);
                    border-radius: 10px;
                    padding: 12px;
                    margin-bottom: 10px;
                }
                .rsg-commission-hero-value {
                    font-size: 22px;
                    font-weight: 700;
                    color: #18489d;
                    line-height: 1.1;
                }
                .rsg-commission-hero-label {
                    margin-top: 4px;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    color: var(--text-muted-color, #7b8698);
                }
                .rsg-commission-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 8px;
                    margin-bottom: 10px;
                }
                .rsg-commission-card {
                    border: 1px solid var(--border-color, #e6ebf2);
                    border-radius: 8px;
                    padding: 10px;
                    background: var(--panel-background-color, #fff);
                }
                .rsg-commission-card-danger {
                    background: #fff5f5;
                    border-color: #f3c4c4;
                }
                .rsg-commission-card-value {
                    font-size: 16px;
                    font-weight: 700;
                }
                .rsg-commission-card-label,
                .rsg-commission-subtitle,
                .rsg-commission-label {
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    color: var(--text-muted-color, #7b8698);
                }
                .rsg-commission-stats {
                    border-top: 1px solid var(--border-color, #e6ebf2);
                    border-bottom: 1px solid var(--border-color, #e6ebf2);
                    padding: 6px 0;
                    margin-bottom: 10px;
                }
                .rsg-commission-row,
                .rsg-commission-item {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 8px;
                    padding: 5px 0;
                }
                .rsg-commission-value,
                .rsg-commission-item-side {
                    text-align: right;
                }
                .rsg-commission-subtitle {
                    margin-bottom: 6px;
                }
                .rsg-commission-item {
                    border-bottom: 1px solid var(--border-color, #eef2f6);
                }
                .rsg-commission-item:last-child {
                    border-bottom: none;
                }
                .rsg-commission-item-name {
                    font-size: 12px;
                    font-weight: 600;
                    line-height: 1.3;
                }
                .rsg-commission-item-date {
                    font-size: 11px;
                    color: var(--text-muted-color, #7b8698);
                }
                .rsg-commission-item-amount {
                    margin-top: 4px;
                    font-size: 12px;
                    font-weight: 600;
                }
                .rsg-commission-actions {
                    margin-top: 8px;
                    text-align: right;
                    font-size: 11px;
                }
            </style>
        `,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.summary = this.getDefaultSummary();
            this.isLoading = true;
            this.loadFailed = false;

            this.fetchSummary();
        },

        data: function () {
            var summary = this.summary || this.getDefaultSummary();

            return {
                isLoading: this.isLoading,
                loadFailed: this.loadFailed,
                count: summary.count,
                estimatedCount: summary.estimatedCount,
                postedCount: summary.postedCount,
                overdueCount: summary.overdueCount,
                hasOverdue: summary.overdueAmount > 0,
                pipelineAmountFormatted: this.formatCurrency(summary.pipelineAmount),
                earnedAmountFormatted: this.formatCurrency(summary.earnedAmount),
                overdueAmountFormatted: this.formatCurrency(summary.overdueAmount),
                lastPaymentDateFormatted: this.formatDate(summary.lastPaymentDate),
                nextExpectedDateFormatted: this.formatDate(summary.nextExpectedDate),
                latestCommissions: (summary.latestCommissions || []).map(function (item) {
                    var copy = Object.assign({}, item);
                    copy.amountFormatted = this.formatCurrency(copy.amount);
                    copy.displayDateFormatted = this.formatDate(copy.displayDate);
                    copy.statusStyle = this.getStatusStyle(copy.status);
                    return copy;
                }, this)
            };
        },

        actionRefreshSummary: function () {
            this.fetchSummary();
        },

        fetchSummary: function () {
            this.isLoading = true;
            this.loadFailed = false;
            this.reRender();

            Espo.Ajax.postRequest("Account/action/commissionSummary", {id: this.model.id}).then(
                function (summary) {
                    this.summary = Object.assign(this.getDefaultSummary(), summary || {});
                    this.isLoading = false;
                    this.reRender();
                }.bind(this)
            ).catch(function () {
                this.summary = this.getDefaultSummary();
                this.isLoading = false;
                this.loadFailed = true;
                this.reRender();
            }.bind(this));
        },

        getDefaultSummary: function () {
            return {
                count: 0,
                estimatedCount: 0,
                postedCount: 0,
                overdueCount: 0,
                pipelineAmount: 0,
                earnedAmount: 0,
                overdueAmount: 0,
                lastPaymentDate: null,
                nextExpectedDate: null,
                latestCommissions: []
            };
        },

        formatCurrency: function (value) {
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: "USD",
                maximumFractionDigits: 0
            }).format(value || 0);
        },

        formatDate: function (value) {
            if (!value) {
                return "—";
            }

            var date = new Date(value);

            if (isNaN(date)) {
                return value;
            }

            return date.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric"
            });
        },

        getStatusStyle: function (status) {
            if (status === "Posted") {
                return "success";
            }

            if (status === "Overdue") {
                return "danger";
            }

            return "info";
        }
    });
});
