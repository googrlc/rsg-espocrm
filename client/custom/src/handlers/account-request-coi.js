/************************************************************************
 * RSG "Request COI" action handler (Account detail button)
 *
 * Opens a pre-filled Task create modal for a Certificate of Insurance
 * request: Type=COI Requested, Status=In Progress, Priority=High,
 * assigned to Gretchen, linked to the Account, with the client's NowCerts
 * certificate deep-link dropped into Ref Link.
 *
 * The before-save hook Espo\Custom\Hooks\Task\ApplyCoiDefaults is the
 * server-side safety net (fills the description template + Ref Link for
 * ANY COI task, however it is created).
 ************************************************************************/

define('custom:handlers/account-request-coi', ['action-handler'], function (Dep) {

    // ===================================================================
    // NowCerts insured / certificate deep-link template.
    // {id} is replaced with the Account's NowCerts insured database_id
    // (Account.momentum_client_id).
    //
    // {id} = Account.momentum_client_id (NowCerts insured database_id, a GUID).
    // Lands on the insured's certificate/forms page where COIs are issued.
    // ===================================================================
    var NOWCERTS_INSURED_URL = 'https://www6.nowcerts.com/AMSINS/Insureds/Details/{id}/PdfForms';

    function buildNowCertsUrl(insuredId) {
        if (!insuredId) {
            return null;
        }

        return NOWCERTS_INSURED_URL.replace('{id}', encodeURIComponent(insuredId));
    }

    return Dep.extend({

        requestCoi: function () {
            var view = this.view;
            var account = view.model;
            var accountName = account.get('name') || '';
            var refLink = buildNowCertsUrl(account.get('momentum_client_id'));

            // Assigned User is intentionally left unset — the server-side
            // ApplyCoiDefaults hook resolves Gretchen by username and assigns
            // her on save, so no user id is hardcoded on the client.
            var attributes = {
                taskType: 'COI Requested',
                status: 'In Progress',
                priority: 'High',
                name: 'COI Request — ' + (accountName || '[Client]'),
                parentId: account.id,
                parentType: 'Account',
                parentName: accountName,
                accountId: account.id,
                accountName: accountName,
                taskSource: 'Account'
            };

            if (refLink) {
                attributes.refLink = refLink;
            }

            view.createView('coiCreate', 'views/modals/edit', {
                scope: 'Task',
                attributes: attributes
            }, function (modal) {
                modal.render();

                view.listenToOnce(modal, 'after:save', function () {
                    Espo.Ui.success('COI request task created.');

                    // Best-effort refresh of the Account's Tasks panel.
                    var panels = view.getView && view.getView('bottomPanels');
                    var tasksPanel = panels && panels.getView && panels.getView('tasks');

                    if (tasksPanel && tasksPanel.collection && typeof tasksPanel.collection.fetch === 'function') {
                        tasksPanel.collection.fetch();
                    }
                });
            });
        }
    });
});
