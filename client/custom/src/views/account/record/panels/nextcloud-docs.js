/************************************************************************
 * RSG Nextcloud Docs side panel
 *
 * Lists a client's Nextcloud document folder via the server-side
 * GetNextcloudDocs endpoint (WebDAV PROPFIND). The Nextcloud app password
 * lives in server config and is never exposed to the browser.
 *
 * Paste a Nextcloud folder share URL (?dir=/...) to link it; the panel
 * then fetches and lists the folder contents with open-in-Nextcloud links.
 ************************************************************************/

define("custom:views/account/record/panels/nextcloud-docs", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-nc-panel">
                {{#if hasFolderUrl}}
                    <div class="rsg-nc-head">
                        <a href="{{folderUrl}}" target="_blank" rel="noopener noreferrer" class="rsg-nc-open">
                            <span class="rsg-nc-icon fas fa-folder-open"></span>
                            <span class="rsg-nc-open-text">Open Folder</span>
                            <span class="rsg-nc-arrow">&nearr;</span>
                        </a>
                        <span class="rsg-nc-head-actions">
                            <a href="javascript:" class="text-muted" data-action="refreshList" title="Refresh">
                                <span class="fas fa-sync-alt"></span>
                            </a>
                            <a href="javascript:" class="text-muted" data-action="showInput" title="Change">change</a>
                        </span>
                    </div>
                    <div class="rsg-nc-list" data-role="list">
                        <div class="text-muted rsg-nc-loading">Loading\u2026</div>
                    </div>
                    <div class="rsg-nc-edit-wrap hidden">
                        <input type="text"
                            class="form-control input-sm rsg-nc-url-input"
                            placeholder="Paste Nextcloud folder URL"
                            data-name="nextcloudUrlEditInput">
                        <div class="rsg-nc-edit-actions">
                            <button class="btn btn-primary btn-xs-text" data-action="saveFolderUrl">Save</button>
                            <button class="btn btn-default btn-xs-text" data-action="cancelEdit">Cancel</button>
                        </div>
                    </div>
                {{else}}
                    <div class="rsg-nc-empty">
                        <div class="rsg-nc-input-wrap">
                            <input type="text"
                                class="form-control input-sm rsg-nc-url-input"
                                placeholder="Paste Nextcloud folder URL"
                                data-name="nextcloudUrlInput">
                            <button class="btn btn-primary btn-xs-text rsg-nc-save-btn"
                                data-action="saveFolderUrl">Link</button>
                        </div>
                        <div class="text-muted rsg-nc-hint">In Nextcloud, open the client folder &rarr; Share &rarr; Copy link, then paste it here.</div>
                    </div>
                {{/if}}
            </div>
            <style>
                .rsg-nc-panel { padding: 2px 0; }
                .rsg-nc-head { display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 6px; }
                .rsg-nc-open { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;
                    padding: 8px 10px; border-radius: 6px; background: var(--inset-panel-background-color, #f5f8fa);
                    border: 1px solid var(--border-color, #e0e0e0); text-decoration: none;
                    color: var(--text-color, #333); font-size: 13px; font-weight: 500; transition: background 0.15s; }
                .rsg-nc-open:hover { background: #e8f4ff; border-color: #2262e8; color: #143a93; }
                .rsg-nc-icon { flex-shrink: 0; color: #2262e8; }
                .rsg-nc-open-text { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .rsg-nc-arrow { font-size: 14px; opacity: 0.6; }
                .rsg-nc-head-actions { display: flex; gap: 10px; flex-shrink: 0; font-size: 11px; }
                .rsg-nc-head-actions a { cursor: pointer; }
                .rsg-nc-head-actions a:hover { color: #2262e8 !important; }
                .rsg-nc-list { margin-top: 2px; }
                .rsg-nc-list ul { list-style: none; margin: 0; padding: 0; }
                .rsg-nc-item { display: flex; align-items: center; gap: 8px; padding: 5px 4px;
                    border-bottom: 1px solid var(--border-color, #e8e8e8); font-size: 13px; }
                .rsg-nc-item:last-child { border-bottom: none; }
                .rsg-nc-item a { display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;
                    text-decoration: none; color: var(--text-color, #333); }
                .rsg-nc-item a:hover { color: #2262e8; }
                .rsg-nc-item .rsg-nc-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .rsg-nc-item .fas { flex-shrink: 0; opacity: 0.7; }
                .rsg-nc-item.is-dir .fas { color: #e0a800; }
                .rsg-nc-item.is-file .fas { color: #888; }
                .rsg-nc-loading, .rsg-nc-error, .rsg-nc-empty-list { padding: 4px 0; font-size: 12px; }
                .rsg-nc-error { color: var(--danger-color, #c0392b); }
                .rsg-nc-input-wrap { display: flex; gap: 6px; align-items: center; }
                .rsg-nc-save-btn { white-space: nowrap; }
                .rsg-nc-hint { margin-top: 6px; font-size: 11px; line-height: 1.4; }
                .rsg-nc-edit-wrap { margin-top: 8px; }
                .rsg-nc-edit-wrap.hidden { display: none; }
                .rsg-nc-edit-actions { display: flex; gap: 6px; margin-top: 6px; justify-content: flex-end; }
            </style>
        `,

        data: function () {
            var folderUrl = this.model.get("nextcloud_folder_url");

            return {
                hasFolderUrl: !!folderUrl,
                folderUrl: folderUrl || "#"
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:nextcloud_folder_url", this.reRender, this);
        },

        afterRender: function () {
            if (this.model.get("nextcloud_folder_url")) {
                this.fetchList();
            }
        },

        fetchList: function () {
            var self = this;
            var $list = this.$el.find('[data-role="list"]');

            if (!$list.length) {
                return;
            }

            $list.html('<div class="text-muted rsg-nc-loading">Loading\u2026</div>');

            Espo.Ajax.getRequest("Account/" + this.model.id + "/nextcloudDocs")
                .then(function (res) {
                    self.renderList($list, res);
                })
                .catch(function () {
                    $list.html('<div class="rsg-nc-error">Could not load folder.</div>');
                });
        },

        renderList: function ($list, res) {
            var self = this;

            if (res && res.error) {
                $list.html('<div class="rsg-nc-error">' + self.escapeHtml(res.error) + '</div>');
                return;
            }

            var items = (res && res.items) || [];

            if (!items.length) {
                $list.html('<div class="text-muted rsg-nc-empty-list">Folder is empty.</div>');
                return;
            }

            var html = '<ul>';

            items.forEach(function (item) {
                var cls = item.isDir ? "is-dir" : "is-file";
                var icon = item.isDir ? "fas fa-folder" : "fas fa-file";

                html += '<li class="rsg-nc-item ' + cls + '">' +
                    '<a href="' + item.openUrl + '" target="_blank" rel="noopener noreferrer" title="' +
                    self.escapeAttr(item.name) + '">' +
                    '<span class="' + icon + '"></span>' +
                    '<span class="rsg-nc-name">' + self.escapeHtml(item.name) + '</span>' +
                    '</a></li>';
            });

            html += '</ul>';
            $list.html(html);
        },

        escapeHtml: function (s) {
            return String(s == null ? "" : s)
                .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
        },

        escapeAttr: function (s) {
            return this.escapeHtml(s);
        },

        actionShowInput: function () {
            var $editWrap = this.$el.find(".rsg-nc-edit-wrap");
            var $input = $editWrap.find("[data-name='nextcloudUrlEditInput']");

            $editWrap.removeClass("hidden");
            $input.val(this.model.get("nextcloud_folder_url") || "");
            $input.focus();
        },

        actionCancelEdit: function () {
            this.$el.find(".rsg-nc-edit-wrap").addClass("hidden");
        },

        actionRefreshList: function () {
            this.fetchList();
        },

        actionSaveFolderUrl: function () {
            var $input = this.$el.find("[data-name='nextcloudUrlInput']");

            if (!$input.length || !$input.is(":visible")) {
                $input = this.$el.find("[data-name='nextcloudUrlEditInput']");
            }

            var url = ($input.val() || "").trim();

            if (!url) {
                return;
            }

            if (url.indexOf("dir=") === -1) {
                Espo.Ui.warning("Paste a Nextcloud folder URL (it should contain \"dir=\").");
                return;
            }

            this.model.set("nextcloud_folder_url", url);

            this.model.save({nextcloud_folder_url: url}, {patch: true}).then(
                function () {
                    Espo.Ui.success("Nextcloud folder linked.");
                }.bind(this)
            );
        }
    });
});
