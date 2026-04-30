define("custom:views/account/record/panels/record-info", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-record-info-panel">
                <div class="rsg-record-info-row">
                    <span class="rsg-record-info-label">Assigned</span>
                    <span class="rsg-record-info-value">
                        {{#if assignedUserId}}
                            <a href="#User/view/{{assignedUserId}}">{{assignedUserName}}</a>
                        {{else}}
                            <span class="text-muted">{{assignedUserName}}</span>
                        {{/if}}
                    </span>
                </div>
                <div class="rsg-record-info-row">
                    <span class="rsg-record-info-label">Created</span>
                    <span class="rsg-record-info-value">{{createdAtFormatted}}</span>
                </div>
                <div class="rsg-record-info-row">
                    <span class="rsg-record-info-label">Created By</span>
                    <span class="rsg-record-info-value">
                        {{#if createdById}}
                            <a href="#User/view/{{createdById}}">{{createdByName}}</a>
                        {{else}}
                            <span class="text-muted">{{createdByName}}</span>
                        {{/if}}
                    </span>
                </div>
                <div class="rsg-record-info-row">
                    <span class="rsg-record-info-label">Modified</span>
                    <span class="rsg-record-info-value">{{modifiedAtFormatted}}</span>
                </div>
                <div class="rsg-record-info-row">
                    <span class="rsg-record-info-label">NowCerts ID</span>
                    <span class="rsg-record-info-value">
                        {{#if momentumClientId}}
                            <code>{{momentumClientId}}</code>
                        {{else}}
                            <span class="text-muted">&mdash;</span>
                        {{/if}}
                    </span>
                </div>
            </div>
            <style>
                .rsg-record-info-panel { padding: 4px 0; }
                .rsg-record-info-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 8px;
                    padding: 5px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                }
                .rsg-record-info-row:last-child { border-bottom: 0; }
                .rsg-record-info-label {
                    min-width: 78px;
                    color: var(--text-muted-color, #999);
                    font-size: 11px;
                    font-weight: 600;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                    white-space: nowrap;
                }
                .rsg-record-info-value {
                    color: var(--text-color, #333);
                    font-size: 12px;
                    text-align: right;
                    word-break: break-word;
                }
                .rsg-record-info-value code {
                    background: var(--inset-panel-background-color, #f5f5f5);
                    border-radius: 3px;
                    color: var(--primary-color, #3a87b0);
                    font-size: 11px;
                    padding: 1px 5px;
                }
            </style>
        `,

        data: function () {
            return {
                assignedUserId: this.model.get("assignedUserId"),
                assignedUserName: this.model.get("assignedUserName") || "Unassigned",
                createdAtFormatted: this.formatDateTime(this.model.get("createdAt")),
                createdById: this.model.get("createdById"),
                createdByName: this.model.get("createdByName") || "-",
                modifiedAtFormatted: this.formatDateTime(this.model.get("modifiedAt")),
                momentumClientId: this.model.get("momentum_client_id") || this.model.get("momentumClientId")
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(
                this.model,
                "change:assignedUserId change:assignedUserName change:modifiedAt change:momentum_client_id change:momentumClientId",
                this.reRender,
                this
            );
        },

        formatDateTime: function (value) {
            if (!value) return "-";

            if (this.getDateTime) {
                return this.getDateTime().toDisplay(value);
            }

            return value;
        }
    });
});
