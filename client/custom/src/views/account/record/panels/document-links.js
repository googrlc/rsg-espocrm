/************************************************************************
 * RSG Document Links side panel
 *
 * A flexible list of titled web links (HTML proposals, benefits guides,
 * etc.) stored on the Account as JSON in the `documentLinks` field.
 * Add / edit / remove inline; each link opens in a new tab.
 *
 * For actual FILES, use the Documents field / Google Drive sync instead —
 * this panel is for links to hosted web pages.
 ************************************************************************/

define("custom:views/account/record/panels/document-links", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-doc-links">
                {{#if links.length}}
                <ul class="rsg-doc-links-list">
                    {{#each links}}
                    <li class="rsg-doc-link-row">
                        <a href="{{url}}" target="_blank" rel="noopener noreferrer" class="rsg-doc-link" title="{{url}}">
                            <span class="rsg-doc-label">{{label}}</span>
                            <span class="rsg-doc-arrow">&nearr;</span>
                        </a>
                        <span class="rsg-doc-row-actions">
                            <a href="javascript:" data-action="editLink" data-index="{{index}}" title="Edit">
                                <span class="fas fa-pencil-alt"></span>
                            </a>
                            <a href="javascript:" data-action="removeLink" data-index="{{index}}" title="Remove">
                                <span class="fas fa-times"></span>
                            </a>
                        </span>
                    </li>
                    {{/each}}
                </ul>
                {{else}}
                <div class="rsg-doc-empty text-muted">No links yet.</div>
                {{/if}}
                <button type="button" class="btn btn-default btn-xs-text rsg-doc-add" data-action="addLink">
                    <span class="fas fa-plus"></span> Add Link
                </button>
            </div>
            <style>
                .rsg-doc-links { padding: 2px 0; }
                .rsg-doc-links-list { list-style: none; margin: 0 0 8px; padding: 0; }
                .rsg-doc-link-row {
                    display: flex; align-items: center; justify-content: space-between;
                    gap: 6px; padding: 5px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                }
                .rsg-doc-link-row:last-child { border-bottom: none; }
                .rsg-doc-link {
                    flex: 1; min-width: 0; display: flex; align-items: center; gap: 4px;
                    font-size: 13px; text-decoration: none;
                }
                .rsg-doc-label {
                    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                }
                .rsg-doc-arrow { opacity: 0.6; flex-shrink: 0; }
                .rsg-doc-row-actions { display: flex; gap: 8px; flex-shrink: 0; }
                .rsg-doc-row-actions a { color: var(--text-muted-color, #999); font-size: 12px; }
                .rsg-doc-row-actions a:hover { color: var(--danger-color, #d9534f); }
                .rsg-doc-row-actions a[data-action="editLink"]:hover { color: var(--primary-color, #1a73e8); }
                .rsg-doc-empty { padding: 4px 0 8px; font-size: 12px; }
            </style>
        `,

        data: function () {
            return {
                links: this.getLinks().map(function (link, i) {
                    return { label: link.label, url: link.url, index: i };
                })
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);
            this.listenTo(this.model, "change:documentLinks", this.reRender, this);
        },

        getLinks: function () {
            var raw = this.model.get("documentLinks");

            if (!raw) {
                return [];
            }

            try {
                var arr = JSON.parse(raw);

                if (!Array.isArray(arr)) {
                    return [];
                }

                return arr.filter(function (item) {
                    return item && item.url;
                });
            } catch (e) {
                return [];
            }
        },

        saveLinks: function (links, successMessage) {
            var self = this;
            var json = JSON.stringify(links);

            // Optimistic: the change listener re-renders immediately.
            this.model.set("documentLinks", json);

            this.model.save({ documentLinks: json }, { patch: true })
                .then(function () {
                    Espo.Ui.success(successMessage);
                })
                .catch(function () {
                    Espo.Ui.error("Could not save the link.");
                    self.model.fetch().then(function () {
                        self.reRender();
                    });
                });
        },

        actionAddLink: function () {
            this.openEditor(null, null);
        },

        actionEditLink: function (data) {
            var index = parseInt(data.index, 10);
            var link = this.getLinks()[index];

            if (!link) {
                return;
            }

            this.openEditor(index, link);
        },

        actionRemoveLink: function (data) {
            var index = parseInt(data.index, 10);
            var links = this.getLinks();

            if (index < 0 || index >= links.length) {
                return;
            }

            if (!confirm('Remove "' + (links[index].label || links[index].url) + '"?')) {
                return;
            }

            links.splice(index, 1);
            this.saveLinks(links, "Link removed.");
        },

        openEditor: function (index, link) {
            var self = this;

            this.createView("editor", "custom:views/account/modals/document-link-edit", {
                index: index,
                label: link ? link.label : "",
                url: link ? link.url : ""
            }, function (view) {
                view.render();

                self.listenToOnce(view, "save", function (payload) {
                    var links = self.getLinks();

                    if (payload.index === null || payload.index === undefined) {
                        links.push({ label: payload.label, url: payload.url });
                    } else {
                        links[payload.index] = { label: payload.label, url: payload.url };
                    }

                    self.saveLinks(links, "Link saved.");
                });
            });
        }
    });
});
