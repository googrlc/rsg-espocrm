/************************************************************************
 * RSG Google Drive Sidebar Panel
 * Shows the client Google Drive folder link with quick-open button.
 * Allows inline paste of a Drive URL directly from the side panel.
 ************************************************************************/

define("custom:views/account/record/panels/google-drive", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-gdrive-panel">
                {{#if hasDriveUrl}}
                    <div class="rsg-gdrive-link-row">
                        <a href="{{driveUrl}}" target="_blank" rel="noopener noreferrer" class="rsg-gdrive-link">
                            <span class="rsg-gdrive-icon">
                                <svg width="18" height="18" viewBox="0 0 87.3 78" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.6 66.85l3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3L28.2 48.5H0c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                                    <path d="M43.65 25L29.2 0c-1.35.8-2.5 1.9-3.3 3.3L1.2 44c-.8 1.4-1.2 2.95-1.2 4.5h28.2z" fill="#00ac47"/>
                                    <path d="M73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75L86.1 57c.8-1.4 1.2-2.95 1.2-4.5H59.1l5.9 11.75z" fill="#ea4335"/>
                                    <path d="M43.65 25L58.1 0H29.2z" fill="#00832d"/>
                                    <path d="M59.1 48.5H28.2L13.75 76.8h58.95z" fill="#2684fc"/>
                                    <path d="M73.4 24.5l-14.45-25c-1.35-.8-2.9-1.25-4.5-1.25H32.9c-1.6 0-3.15.45-4.5 1.25L43.65 25H59.1l14.3 23.5H87.3c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
                                </svg>
                            </span>
                            <span class="rsg-gdrive-text">Open Drive Folder</span>
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
                                placeholder="Paste Google Drive folder URL"
                                data-name="driveUrlInput">
                            <button class="btn btn-primary btn-xs-text rsg-gdrive-save-btn"
                                data-action="saveDriveUrl">Link</button>
                        </div>
                    </div>
                {{/if}}

                <div class="rsg-gdrive-edit-wrap hidden">
                    <input type="text"
                        class="form-control input-sm rsg-gdrive-url-input"
                        placeholder="Paste Google Drive folder URL"
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
            var folderUrl = this.model.get("googleDriveFolderUrl");
            return {
                hasDriveUrl: !!folderUrl,
                driveUrl: folderUrl || "#"
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:googleDriveFolderUrl", this.reRender, this);
        },

        actionShowInput: function () {
            var $editWrap = this.$el.find(".rsg-gdrive-edit-wrap");
            var $input = $editWrap.find("[data-name='driveUrlEditInput']");

            $editWrap.removeClass("hidden");
            $input.val(this.model.get("googleDriveFolderUrl") || "");
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

            var folderUrl = url;
            var folderId = this.extractFolderId(url);

            if (!folderId && url.indexOf("drive.google.com") === -1 && url.indexOf("docs.google.com") === -1) {
                Espo.Ui.warning("Please paste a valid Google Drive folder URL or folder ID.");
                return;
            }

            if (folderId) {
                folderUrl = "https://drive.google.com/drive/u/0/folders/" + encodeURIComponent(folderId);
            }

            this.model.set("googleDriveFolderUrl", folderUrl);

            this.model.save({googleDriveFolderUrl: folderUrl}, {patch: true}).then(
                function () {
                    Espo.Ui.success("Drive folder linked.");
                }.bind(this)
            );
        },

        extractFolderId: function (value) {
            var match = value.match(/\/folders\/([^/?#]+)/);
            if (match && match[1]) {
                return match[1];
            }

            if (/^[A-Za-z0-9_-]{10,}$/.test(value)) {
                return value;
            }

            return null;
        }
    });
});
