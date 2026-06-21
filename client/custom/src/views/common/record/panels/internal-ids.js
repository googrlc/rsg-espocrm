define("custom:views/common/record/panels/internal-ids", ["view"], function (Dep) {

    var NOWCERTS_INSURED_URL = "https://www6.nowcerts.com/AMSINS/Insureds/Details/{id}/PdfForms";

    return Dep.extend({

        templateContent: `
            <div class="rsg-internal-ids-panel">
                {{#each rows}}
                    <div class="rsg-internal-row">
                        <span class="rsg-internal-label">{{label}}</span>
                        <span class="rsg-internal-value">{{{value}}}</span>
                    </div>
                {{/each}}
            </div>
            <style>
                .rsg-internal-ids-panel { padding: 4px 0; }
                .rsg-internal-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 10px;
                    padding: 6px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                }
                .rsg-internal-row:last-child { border-bottom: none; }
                .rsg-internal-label {
                    min-width: 88px;
                    color: var(--text-muted-color, #999);
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }
                .rsg-internal-value {
                    color: var(--text-color, #333);
                    font-size: 12px;
                    text-align: right;
                    word-break: break-all;
                }
                .rsg-internal-value a {
                    color: var(--link-color, #337ab7);
                    font-weight: 600;
                }
                .rsg-internal-value code {
                    background: var(--inset-panel-background-color, #f5f5f5);
                    border-radius: 3px;
                    color: var(--primary-color, #3a87b0);
                    font-family: monospace;
                    font-size: 11px;
                    padding: 1px 5px;
                }
                .rsg-internal-muted {
                    color: var(--text-muted-color, #999);
                }
            </style>
        `,

        data: function () {
            return {
                rows: this.buildRows()
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(
                this.model,
                [
                    "change:momentum_client_id",
                    "change:momentumClientId",
                    "change:momentumPolicyId",
                    "change:momentumTaskId",
                    "change:insuredMomentumId",
                    "change:momentum_last_synced",
                    "change:momentumLastSynced",
                    "change:sync_status",
                    "change:syncStatus",
                    "change:google_drive_folder_url",
                    "change:contactDriveFolderUrl",
                    "change:driveFolderUrl",
                    "change:intel_pack_run",
                    "change:intelPackRun",
                    "change:intel_run",
                    "change:intel_pack_last_run",
                    "change:intelPackLastRun",
                    "change:intel_run_date"
                ].join(" "),
                this.reRender,
                this
            );
        },

        buildRows: function () {
            var rows = [];
            var primaryId = this.firstValue([
                "momentum_client_id",
                "momentumClientId",
                "momentumPolicyId",
                "momentumTaskId"
            ]);
            var insuredId = this.model.get("insuredMomentumId");
            var lastSynced = this.firstValue(["momentum_last_synced", "momentumLastSynced"]);
            var syncStatus = this.firstValue(["sync_status", "syncStatus"]);
            var driveFolder = this.firstValue(["google_drive_folder_url", "contactDriveFolderUrl", "driveFolderUrl"]);
            var intelRun = this.firstDefined(["intel_pack_run", "intelPackRun", "intel_run"]);
            var intelLastRun = this.firstValue(["intel_pack_last_run", "intelPackLastRun", "intel_run_date"]);

            if (primaryId) {
                rows.push({
                    label: "NowCerts ID",
                    value: this.formatCode(primaryId, this.buildNowCertsUrl(primaryId))
                });
            }

            if (insuredId && insuredId !== primaryId) {
                rows.push({
                    label: "Insured ID",
                    value: this.formatCode(insuredId, this.buildNowCertsUrl(insuredId))
                });
            }

            if (lastSynced) {
                rows.push({
                    label: "Last Synced",
                    value: this.escapeHtml(this.formatDateTime(lastSynced))
                });
            }

            if (syncStatus) {
                rows.push({
                    label: "Sync Status",
                    value: this.escapeHtml(syncStatus)
                });
            }

            if (driveFolder) {
                rows.push({
                    label: "Drive Folder",
                    value: '<a href="' + this.escapeAttr(driveFolder) + '" target="_blank" rel="noopener noreferrer">Linked &nearr;</a>'
                });
            }

            if (typeof intelRun !== "undefined") {
                rows.push({
                    label: "Intel Pack",
                    value: intelRun ? '<span class="label label-success" style="font-size:10px;">Run</span>' : this.muted("Not run")
                });
            }

            if (intelLastRun) {
                rows.push({
                    label: "Intel Last Run",
                    value: this.escapeHtml(this.formatDateTime(intelLastRun))
                });
            }

            if (!rows.length) {
                rows.push({
                    label: "Internal IDs",
                    value: this.muted("None")
                });
            }

            return rows;
        },

        firstValue: function (fields) {
            for (var i = 0; i < fields.length; i++) {
                var value = this.model.get(fields[i]);
                if (value) return value;
            }
            return null;
        },

        firstDefined: function (fields) {
            for (var i = 0; i < fields.length; i++) {
                var value = this.model.get(fields[i]);
                if (typeof value !== "undefined") return value;
            }
        },

        formatCode: function (value, url) {
            var code = "<code>" + this.escapeHtml(value) + "</code>";

            if (!url) {
                return code;
            }

            return '<a href="' + this.escapeAttr(url) + '" target="_blank" rel="noopener noreferrer">' + code + ' &nearr;</a>';
        },

        buildNowCertsUrl: function (id) {
            if (!id) return null;

            return NOWCERTS_INSURED_URL.replace("{id}", encodeURIComponent(id));
        },

        formatDateTime: function (value) {
            if (!value) return "";

            if (this.getDateTime) {
                return this.getDateTime().toDisplay(value);
            }

            return value;
        },

        muted: function (value) {
            return '<span class="rsg-internal-muted">' + this.escapeHtml(value) + '</span>';
        },

        escapeHtml: function (value) {
            return String(value == null ? "" : value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        },

        escapeAttr: function (value) {
            return this.escapeHtml(value).replace(/`/g, "&#96;");
        }
    });
});
