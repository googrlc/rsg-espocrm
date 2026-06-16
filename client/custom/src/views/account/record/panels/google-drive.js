/************************************************************************
 * RSG Client Documents Sidebar Panel
 * Shows the client document folder link (Nextcloud, Google Drive, etc.)
 * with quick-open button. Allows inline paste of any URL.
 ************************************************************************/

define("custom:views/account/record/panels/google-drive", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-gdrive-panel">
                {{#if hasDriveUrl}}
                    <div class="rsg-gdrive-link-row">
                        <a href="{{driveUrl}}" target="_blank" rel="noopener noreferrer" class="rsg-gdrive-link">
                            <span class="rsg-gdrive-icon">
                                <span class="fas fa-folder-open" style="font-size:18px;color:#5f6368;"></span>
                            </span>
                            <span class="rsg-gdrive-text">Open Client Documents</span>
                            <span class="rsg-gdrive-arrow">&nearr;</span>
                        </a>
                    </div>
                    <div class="rsg-gdrive-actions">
                        <a href="javascript:" class="rsg-gdrive-change text-muted" data-action="showInput">change</a>
                    </div>
                {{else}}
                    <div class="rsg-gdrive-empty">
                        <div class="rsg-gdrive-input-wrap">
                            <input type="text"
                                class="form-control input-sm rsg-gdrive-url-input"
                                placeholder="Paste document folder URL"
                                data-name="driveUrlInput">
                            <button class="btn btn-primary btn-xs-text rsg-gdrive-save-btn"
                                data-action="saveDriveUrl">Link</button>
                        </div>
                    </div>
                {{/if}}

                <div class="rsg-gdrive-edit-wrap hidden">
                    <input type="text"
                        class="form-control input-sm rsg-gdrive-url-input"
                        placeholder="Paste document folder URL"
                        data-name="driveUrlEditInput">
                    <div class="rsg-gdrive-edit-actions">
                        <button class="btn btn-primary btn-xs-text" data-action="saveDriveUrl">Save</button>
                        <button class="btn btn-default btn-xs-text" data-action="cancelEdit">Cancel</button>
                    </div>
                </div>
            </div>
            <style>
                .rsg-gdrive-panel { padding: 2px 0; }
                .rsg-gdrive-link-row { display: flex; align-items: center; }
                .rsg-gdrive-link {
                    display: flex; align-items: center; gap: 8px;
                    padding: 8px 10px; border-radius: 6px;
                    background: var(--inset-panel-background-color, #f5f8fa);
                    border: 1px solid var(--border-color, #e0e0e0);
                    text-decoration: none; color: var(--text-color, #333);
                    width: 100%; transition: background 0.15s;
                    font-size: 13px; font-weight: 500;
                }
                .rsg-gdrive-link:hover {
                    background: #e8f0fe; border-color: #4285f4; color: #1a73e8;
                }
                .rsg-gdrive-icon { display: flex; align-items: center; flex-shrink: 0; }
                .rsg-gdrive-text { flex: 1; }
                .rsg-gdrive-arrow { font-size: 14px; opacity: 0.6; }
                .rsg-gdrive-actions {
                    text-align: right; margin-top: 4px; font-size: 11px;
                }
                .rsg-gdrive-change { cursor: pointer; }
                .rsg-gdrive-change:hover { color: #1a73e8 !important; }
                .rsg-gdrive-input-wrap {
                    display: flex; gap: 6px; align-items: center;
                }
                .rsg-gdrive-url-input { font-size: 12px; }
                .rsg-gdrive-edit-wrap {
                    margin-top: 8px;
                }
                .rsg-gdrive-edit-wrap.hidden { display: none; }
                .rsg-gdrive-edit-actions {
                    display: flex; gap: 6px; margin-top: 6px; justify-content: flex-end;
                }
                .rsg-gdrive-empty { padding: 4px 0; line-height: 1.5; }
            </style>
        `,

        data: function () {
            var folderUrl = this.model.get("google_drive_folder_url");
            return {
                hasDriveUrl: !!folderUrl,
                driveUrl: folderUrl || "#"
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:google_drive_folder_url", this.reRender, this);
        },

        actionShowInput: function () {
            var $editWrap = this.$el.find(".rsg-gdrive-edit-wrap");
            var $input = $editWrap.find("[data-name='driveUrlEditInput']");

            $editWrap.removeClass("hidden");
            $input.val(this.model.get("google_drive_folder_url") || "");
            $input.focus();
        },

        actionCancelEdit: function () {
            this.$el.find(".rsg-gdrive-edit-wrap").addClass("hidden");
        },

        actionSaveDriveUrl: function () {
            var $input = this.$el.find("[data-name='driveUrlInput']");

            if (!$input.length || !$input.is(":visible")) {
                $input = this.$el.find("[data-name='driveUrlEditInput']");
            }

            var url = ($input.val() || "").trim();

            if (!url) return;

            if (!/^https?:\/\//i.test(url)) {
                Espo.Ui.warning("Please paste a valid URL (starting with http:// or https://).");
                return;
            }

            this.model.set("google_drive_folder_url", url);

            this.model.save({google_drive_folder_url: url}, {patch: true}).then(
                function () {
                    Espo.Ui.success("Document folder linked.");
                }.bind(this)
            );
        }
    });
});
