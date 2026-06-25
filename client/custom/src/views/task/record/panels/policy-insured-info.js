define('custom:views/task/record/panels/policy-insured-info', ['view'], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-policy-side-panel">
                {{#each rows}}
                    <div class="rsg-policy-side-row">
                        <span class="rsg-policy-side-label">{{label}}</span>
                        <span class="rsg-policy-side-value">{{{value}}}</span>
                    </div>
                {{/each}}
            </div>
            <style>
                .rsg-policy-side-panel { padding: 4px 0; }
                .rsg-policy-side-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 10px;
                    padding: 6px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                }
                .rsg-policy-side-row:last-child { border-bottom: 0; }
                .rsg-policy-side-label {
                    min-width: 80px;
                    color: var(--text-muted-color, #999);
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }
                .rsg-policy-side-value {
                    color: var(--text-color, #333);
                    font-size: 12px;
                    text-align: right;
                    word-break: break-word;
                }
                .rsg-policy-side-value a {
                    color: var(--link-color, #337ab7);
                    text-decoration: none;
                }
                .rsg-policy-side-value a:hover { text-decoration: underline; }
                .rsg-policy-side-empty { color: var(--text-muted-color, #999); font-style: italic; }
            </style>
        `,

        setup: function () {
            this.buildRows();
            this.listenTo(this.model, 'change', function () {
                this.buildRows();
                if (this.isRendered) {
                    this.reRender();
                }
            });
        },

        buildRows: function () {
            var rows = [];

            var accountId = this.model.get('accountId');
            var accountName = this.model.get('accountName');
            if (accountId) {
                rows.push({
                    label: this.translate('Insured', 'labels', 'Task'),
                    value: '<a href="#Account/' + accountId + '">' + this.escapeHtml(accountName || '') + '</a>'
                });
            } else {
                rows.push({label: this.translate('Insured', 'labels', 'Task'), value: '<span class="rsg-policy-side-empty">—</span>'});
            }

            var contactId = this.model.get('contactId');
            var contactName = this.model.get('contactName');
            if (contactId) {
                rows.push({
                    label: this.translate('Contact', 'labels', 'Task'),
                    value: '<a href="#Contact/' + contactId + '">' + this.escapeHtml(contactName || '') + '</a>'
                });
            } else {
                rows.push({label: this.translate('Contact', 'labels', 'Task'), value: '<span class="rsg-policy-side-empty">—</span>'});
            }

            var policyIds = this.model.get('policiesIds') || [];
            var policyNames = this.model.get('policiesNames') || {};
            if (policyIds.length > 0) {
                var links = policyIds.map(function (id) {
                    var name = policyNames[id] || '';
                    return '<a href="#Policy/' + id + '">' + this.escapeHtml(name) + '</a>';
                }, this);
                rows.push({label: 'Policies', value: links.join('<br>')});
            } else {
                rows.push({label: 'Policies', value: '<span class="rsg-policy-side-empty">—</span>'});
            }

            var policyType = this.model.get('policyType');
            rows.push({
                label: 'Policy Type',
                value: policyType ? this.escapeHtml(policyType) : '<span class="rsg-policy-side-empty">—</span>'
            });

            var effDate = this.model.get('policyEffectiveDate');
            rows.push({
                label: 'Effective',
                value: effDate ? this.getDateString(effDate) : '<span class="rsg-policy-side-empty">—</span>'
            });

            var expDate = this.model.get('policyExpirationDate');
            rows.push({
                label: 'Expiration',
                value: expDate ? this.getDateString(expDate) : '<span class="rsg-policy-side-empty">—</span>'
            });

            this.rows = rows;
        },

        data: function () {
            return {rows: this.rows};
        },

        escapeHtml: function (text) {
            if (!text) { return ''; }
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        getDateString: function (dateStr) {
            if (!dateStr) { return ''; }
            return dateStr;
        }
    });
});
