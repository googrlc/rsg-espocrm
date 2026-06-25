/************************************************************************
 * RSG Internal IDs Sidebar Panel
 * Shows NowCerts/Momentum AMS identifiers, sync metadata,
 * Google Drive link, and Intel Pack status.
 ************************************************************************/

define("custom:views/account/record/panels/internal-ids", ["view"], function (Dep) {

    // ===================================================================
    // NowCerts insured / certificate deep-link template.
    // {id} is replaced with the Account's NowCerts insured database_id
    // (Account.momentum_client_id).
    //
    // {id} = Account.momentum_client_id (NowCerts insured database_id, a GUID).
    // Lands on the insured's certificate/forms page.
    // ===================================================================
    var NOWCERTS_INSURED_URL = "https://www6.nowcerts.com/AMSINS/Insureds/Details/{id}/PdfForms";

    return Dep.extend({

        templateContent: `
            <div class="rsg-internal-ids-panel">
                <div class="rsg-internal-row">
                    <span class="rsg-internal-label">NowCerts ID</span>
                    <span class="rsg-internal-value">
                        {{#if momentumClientId}}
                            {{#if nowCertsUrl}}
                            <a href="{{nowCertsUrl}}" target="_blank" rel="noopener noreferrer" title="Open in NowCerts">
                                <code>{{momentumClientId}}</code> &nearr;
                            </a>
                            {{else}}
                            <code>{{momentumClientId}}</code>
                            {{/if}}
                        {{else}}
                            <span class="text-muted">&mdash;</span>
                        {{/if}}
                    </span>
                </div>
                <div class="rsg-internal-row">
                    <span class="rsg-internal-label">Last Synced</span>
                    <span class="rsg-internal-value text-muted">
                        {{#if momentumLastSyncedFormatted}}
                            {{momentumLastSyncedFormatted}}
                        {{else}}
                            &mdash;
                        {{/if}}
                    </span>
                </div>
                <div class="rsg-internal-row">
                    <span class="rsg-internal-label">Intel Pack</span>
                    <span class="rsg-internal-value">
                        {{#if intelPackRun}}
                            <span class="label label-success" style="font-size:10px;">Run</span>
                        {{else}}
                            <span class="text-muted">Not run</span>
                        {{/if}}
                    </span>
                </div>
                {{#if intelPackLastRunFormatted}}
                <div class="rsg-internal-row">
                    <span class="rsg-internal-label">Intel Last Run</span>
                    <span class="rsg-internal-value text-muted">
                        {{intelPackLastRunFormatted}}
                    </span>
                </div>
                {{/if}}
            </div>
            <style>
                .rsg-internal-ids-panel {
                    padding: 4px 0;
                }
                .rsg-internal-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    padding: 5px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                    gap: 8px;
                }
                .rsg-internal-row:last-child {
                    border-bottom: none;
                }
                .rsg-internal-label {
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                    color: var(--text-muted-color, #999);
                    white-space: nowrap;
                    min-width: 80px;
                }
                .rsg-internal-value {
                    font-size: 12px;
                    color: var(--text-color, #333);
                    text-align: right;
                    word-break: break-all;
                }
                .rsg-internal-value code {
                    background: var(--inset-panel-background-color, #f5f5f5);
                    padding: 1px 5px;
                    border-radius: 3px;
                    font-size: 11px;
                    color: var(--primary-color, #3a87b0);
                    font-family: monospace;
                }
                .rsg-internal-value a {
                    font-size: 12px;
                }
            </style>
        `,

        data: function () {
            var momentumLastSynced = this.model.get("momentum_last_synced");
            var intelPackLastRun = this.model.get("intel_pack_last_run");

            return {
                momentumClientId: this.model.get("momentum_client_id"),
                nowCertsUrl: this.buildNowCertsUrl(this.model.get("momentum_client_id")),
                momentumLastSyncedFormatted: this.formatDate(momentumLastSynced),
                intelPackRun: this.model.get("intel_pack_run"),
                intelPackLastRunFormatted: this.formatDate(intelPackLastRun)
            };
        },

        buildNowCertsUrl: function (insuredId) {
            if (!insuredId) {
                return null;
            }

            return NOWCERTS_INSURED_URL.replace("{id}", encodeURIComponent(insuredId));
        },

        formatDate: function (val) {
            if (!val) return null;
            try {
                var d = new Date(val);
                return d.toLocaleDateString("en-US", {
                    month: "2-digit", day: "2-digit", year: "numeric",
                    hour: "numeric", minute: "2-digit"
                });
            } catch (e) {
                return val;
            }
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model,
                "change:momentum_client_id change:momentum_last_synced change:intel_pack_run change:intel_pack_last_run",
                this.reRender, this
            );
        }
    });
});
