/************************************************************************
 * RSG "Submit Worksheet" action handler (RenewalWorksheet detail button).
 *
 * Wired from clientDefs/RenewalWorksheet.json:
 *   detail.buttons -> handler: custom:handlers/renewal-worksheet-submit-handler
 *      actionFunction: submitWorksheet
 *      checkVisibilityFunction: isSubmitVisible
 *
 * POSTs /api/v1/RenewalWorksheet/action/submit { id } -> the server-side
 * RenewalWorksheetSubmit service derives completion_type (contact_only /
 * full_review) and sets state=completed. Errors (partial attestations,
 * missing client-contact) are surfaced as a toast so the rep can act.
 ************************************************************************/
define('custom:handlers/renewal-worksheet-submit-handler', ['action-handler'], function (Dep) {

    return Dep.extend({

        // Hide once the worksheet is already completed.
        isSubmitVisible: function () {
            return this.view.model.get('state') !== 'completed';
        },

        submitWorksheet: function () {
            var view = this.view;
            var model = view.model;
            var id = model.get('id');

            if (!id) {
                return;
            }

            if (!confirm('Submit this worksheet?\n\ncompletion_type will be derived from the attestations ' +
                '(contact_only if none are set, full_review if all required are set) and state will be set to completed.')) {
                return;
            }

            Espo.Ajax.postRequest('RenewalWorksheet/action/submit', {id: id}).then(function (res) {
                Espo.Ui.success('Worksheet submitted — ' + (res.completion_type || 'done'));
                if (typeof model.fetch === 'function') {
                    model.fetch();
                }
            }.bind(this)).fail(function (xhr) {
                var message = 'Submit failed.';
                try {
                    if (xhr && xhr.responseText) {
                        message = (JSON.parse(xhr.responseText).message) || message;
                    }
                } catch (e) { /* keep default */ }
                Espo.Ui.error(message);
            });
        }
    });
});
