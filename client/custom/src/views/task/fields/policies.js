/************************************************************************
 * RSG Task "Policies" multi-select (COI and any policy-scoped task).
 *
 * A link-multiple field that:
 *   - limits the select dialog to policies belonging to the task's Insured
 *     (Account) — mirrors the getSelectFilters pattern used by the
 *     assigned-user field;
 *   - caps the selection at 5 policies (client-side; the ApplyCoiDefaults
 *     hook is the server-side 1–5 safety net);
 *   - clears any picked policies if the Insured is changed, so a policy from
 *     a previously chosen account can't linger;
 *   - is select-only (no inline Policy creation from the picker).
 ************************************************************************/

define('custom:views/task/fields/policies', ['views/fields/link-multiple'], function (Dep) {

    var MAX_POLICIES = 5;

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            // Select existing policies only — don't offer "Create Policy" here.
            this.createDisabled = true;

            // Run our max-count check as part of the field's validate().
            this.validations = Espo.Utils.clone(this.validations || []);
            this.validations.push('maxCount');

            // If the Insured changes, drop policies that belonged to the old one.
            this.listenTo(this.model, 'change:accountId', function () {
                var ids = this.model.get(this.idsName) || [];

                if (ids.length) {
                    var reset = {};
                    reset[this.idsName] = [];
                    reset[this.nameHashName] = {};
                    this.model.set(reset);
                }
            });
        },

        // Restrict the picker to the current Account's policies.
        // Policy.account stores its FK as the `accountId` attribute.
        getSelectFilters: function () {
            var accountId = this.model.get('accountId');

            if (!accountId) {
                return {};
            }

            return {
                account: {
                    type: 'equals',
                    attribute: 'accountId',
                    value: accountId
                }
            };
        },

        validateMaxCount: function () {
            var ids = this.model.get(this.idsName) || [];

            if (ids.length > MAX_POLICIES) {
                this.showValidationMessage(
                    'Select at most ' + MAX_POLICIES + ' policies.'
                );

                return true;
            }

            return false;
        }
    });
});
