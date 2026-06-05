/************************************************************************
 * RSG Document Link editor modal
 *
 * Collects a { label, url } pair for the Account "Document Links" panel.
 * Opened with: { index: <number|null>, label, url }
 * Triggers:    'save' with { label, url, index }
 ************************************************************************/

define("custom:views/account/modals/document-link-edit", ["views/modal"], function (Dep) {

    return Dep.extend({

        backdrop: true,

        cssName: "document-link-edit-modal",

        shortcutKeys: {
            "Control+Enter": "save"
        },

        templateContent: `
            <div class="document-link-edit">
                <div class="form-group">
                    <label class="control-label">Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control dl-label" placeholder="e.g. 2025 Proposal" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="control-label">Link (URL) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control dl-url" placeholder="https://…">
                </div>
                <div class="dl-error text-danger small" style="display:none; margin-top:4px;"></div>
            </div>
        `,

        setup: function () {
            this.editIndex = (this.options.index === undefined || this.options.index === null)
                ? null
                : this.options.index;

            this.headerText = this.editIndex === null ? "Add Link" : "Edit Link";

            this.buttonList = [
                { name: "save", label: "Save", style: "primary" },
                { name: "cancel", label: this.translate("Cancel") }
            ];
        },

        afterRender: function () {
            this.$label = this.$el.find(".dl-label");
            this.$url = this.$el.find(".dl-url");
            this.$error = this.$el.find(".dl-error");

            this.$label.val(this.options.label || "");
            this.$url.val(this.options.url || "");

            setTimeout(function () {
                if (this.$label) {
                    this.$label.focus();
                }
            }.bind(this), 50);
        },

        actionSave: function () {
            var label = (this.$label.val() || "").trim();
            var url = (this.$url.val() || "").trim();

            if (!label || !url) {
                this.showError("Both a label and a URL are required.");
                return;
            }

            if (!/^https?:\/\//i.test(url)) {
                this.showError("The URL must start with http:// or https://");
                return;
            }

            this.trigger("save", { label: label, url: url, index: this.editIndex });
            this.close();
        },

        showError: function (message) {
            this.$error.text(message).show();
        }
    });
});
