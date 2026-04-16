define("custom:views/policy/record/panels/quick-links", ["view"], function (Dep) {
    return Dep.extend({
        templateContent: `
            <div class="rsg-policy-links">
                {{#if carrierPortalUrl}}
                    <a href="{{carrierPortalUrl}}" target="_blank" rel="noopener noreferrer" class="btn btn-default btn-block">
                        Open Carrier Portal
                    </a>
                {{else}}
                    <div class="text-muted">Carrier portal link will populate when a policy number is set.</div>
                {{/if}}
            </div>
        `,

        data: function () {
            return {
                carrierPortalUrl: this.model.get("carrierPortalUrl")
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:carrierPortalUrl", this.reRender, this);
        }
    });
});
