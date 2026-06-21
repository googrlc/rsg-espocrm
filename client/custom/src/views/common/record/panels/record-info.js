define("custom:views/common/record/panels/record-info", ["view"], function (Dep) {

    return Dep.extend({

        templateContent: `
            <div class="rsg-record-info-panel">
                {{#each rows}}
                    <div class="rsg-record-info-row">
                        <span class="rsg-record-info-label">{{label}}</span>
                        <span class="rsg-record-info-value">{{{value}}}</span>
                    </div>
                {{/each}}
            </div>
            <style>
                .rsg-record-info-panel { padding: 4px 0; }
                .rsg-record-info-row {
                    display: flex;
                    align-items: flex-start;
                    justify-content: space-between;
                    gap: 10px;
                    padding: 6px 0;
                    border-bottom: 1px solid var(--border-color, #e8e8e8);
                }
                .rsg-record-info-row:last-child { border-bottom: 0; }
                .rsg-record-info-label {
                    min-width: 82px;
                    color: var(--text-muted-color, #999);
                    font-size: 11px;
                    font-weight: 700;
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
                .rsg-record-info-value a {
                    color: var(--link-color, #337ab7);
                    font-weight: 600;
                }
                .rsg-record-info-muted {
                    color: var(--text-muted-color, #999);
                }
            </style>
        `,

        data: function () {
            var rows = [];

            rows.push({
                label: "Assigned",
                value: this.linkOrText("User", this.model.get("assignedUserId"), this.model.get("assignedUserName") || "Unassigned")
            });

            rows.push({
                label: "Created",
                value: this.formatStampedUser("createdAt", "createdById", "createdByName")
            });

            rows.push({
                label: "Modified",
                value: this.formatStampedUser("modifiedAt", "modifiedById", "modifiedByName")
            });

            rows.push({
                label: "Followers",
                value: this.formatFollowers()
            });

            return {
                rows: rows
            };
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(
                this.model,
                [
                    "change:assignedUserId",
                    "change:assignedUserName",
                    "change:createdAt",
                    "change:createdById",
                    "change:createdByName",
                    "change:modifiedAt",
                    "change:modifiedById",
                    "change:modifiedByName",
                    "change:followersIds",
                    "change:followersNames",
                    "change:followers"
                ].join(" "),
                this.reRender,
                this
            );
        },

        formatStampedUser: function (dateField, userIdField, userNameField) {
            var date = this.formatDateTime(this.model.get(dateField));
            var userName = this.model.get(userNameField);
            var userId = this.model.get(userIdField);

            if (!date && !userName) {
                return this.muted("-");
            }

            if (!userName) {
                return this.escapeHtml(date);
            }

            return this.escapeHtml(date || "-") + " &middot; " + this.linkOrText("User", userId, userName);
        },

        formatFollowers: function () {
            var names = this.model.get("followersNames");
            var ids = this.model.get("followersIds");
            var followers = this.model.get("followers");
            var list = [];

            if (Array.isArray(followers)) {
                list = followers.map(function (item) {
                    if (typeof item === "string") return { name: item };
                    return {
                        id: item.id,
                        name: item.name || item.userName || item.label
                    };
                });
            } else if (names && typeof names === "object") {
                list = Object.keys(names).map(function (id) {
                    return {
                        id: id,
                        name: names[id]
                    };
                });
            } else if (Array.isArray(names)) {
                list = names.map(function (name, index) {
                    return {
                        id: Array.isArray(ids) ? ids[index] : null,
                        name: name
                    };
                });
            } else if (typeof names === "string" && names) {
                list = names.split(",").map(function (name) {
                    return { name: name.trim() };
                });
            }

            list = list.filter(function (item) {
                return item.name;
            });

            if (!list.length) {
                return this.muted("None");
            }

            return list.map(function (item) {
                return this.linkOrText("User", item.id, item.name);
            }, this).join(", ");
        },

        linkOrText: function (scope, id, text) {
            var label = this.escapeHtml(text || "-");

            if (!id) {
                return label;
            }

            return '<a href="#' + this.escapeAttr(scope) + '/view/' + this.escapeAttr(id) + '">' + label + '</a>';
        },

        formatDateTime: function (value) {
            if (!value) return "";

            if (this.getDateTime) {
                return this.getDateTime().toDisplay(value);
            }

            return value;
        },

        muted: function (value) {
            return '<span class="rsg-record-info-muted">' + this.escapeHtml(value) + '</span>';
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
