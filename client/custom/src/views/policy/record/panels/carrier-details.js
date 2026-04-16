define("custom:views/policy/record/panels/carrier-details", ["view"], function (Dep) {
    return Dep.extend({
        templateContent: `
            <div class="rsg-policy-panel">
                <div class="rsg-policy-row">
                    <div class="rsg-policy-label">Carrier</div>
                    <div class="rsg-policy-value">{{carrier}}</div>
                </div>
                <div class="rsg-policy-row">
                    <div class="rsg-policy-label">Carrier Account</div>
                    <div class="rsg-policy-value">
                        {{#if carrierAccountId}}
                            <a href="#Account/view/{{carrierAccountId}}">{{carrierAccountName}}</a>
                        {{else}}
                            <span class="text-muted">Not linked</span>
                        {{/if}}
                    </div>
                </div>
            </div>
            <style>
                .rsg-policy-row + .rsg-policy-row { margin-top: 10px; }
                .rsg-policy-label {
                    color: var(--text-soft-color, #6b7280);
                    font-size: 11px;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    margin-bottom: 2px;
                }
                .rsg-policy-value { font-size: 13px; font-weight: 600; }
            </style>
        `,

        data: function () {
            return {
                carrier: this.model.get("carrier") || "Not set",
                carrierAccountId: this.model.get("carrierAccountId"),
                carrierAccountName: this.model.get("carrierAccountName") || "Linked carrier"
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:carrier change:carrierAccountId change:carrierAccountName", this.reRender, this);
        }
    });
});
