define('custom:views/task/fields/assigned-user', ['views/fields/link'], function (Dep) {

    // Allowed task owners, resolved by username so no user id is hardcoded.
    // Keep in sync with Hooks/Task/NormalizeAssignmentAndAccount::ALLOWED_USERNAMES.
    var ALLOWED_USERNAMES = ['gretchcoates', 'lamarcoates'];

    // Cached across all field instances on the page (one lookup per session).
    var allowedUsersPromise = null;

    function loadAllowedUsers() {
        if (!allowedUsersPromise) {
            allowedUsersPromise = Espo.Ajax.getRequest('User', {
                select: 'id,name,userName',
                where: [
                    { type: 'in', attribute: 'userName', value: ALLOWED_USERNAMES }
                ],
                maxSize: 10
            }).then(function (response) {
                var map = {};

                (response.list || []).forEach(function (user) {
                    map[user.id] = user.name;
                });

                return map;
            }).catch(function (e) {
                // Don't cache a transient failure — allow a later retry.
                allowedUsersPromise = null;
                throw e;
            });
        }

        return allowedUsersPromise;
    }

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.allowedUsers = {};

            // Only needed where the user can actually pick an assignee.
            if (this.mode === 'edit' || this.mode === 'search') {
                this.wait(
                    loadAllowedUsers()
                        .then(function (map) {
                            this.allowedUsers = map;
                        }.bind(this))
                        .catch(function () {
                            this.allowedUsers = {};
                        }.bind(this))
                );
            }
        },

        getSelectFilters: function () {
            return {
                id: {
                    attribute: 'id',
                    type: 'in',
                    value: Object.keys(this.allowedUsers)
                }
            };
        },

        getCreateAttributes: function () {
            return null;
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this) || {};
            var assignedUserId = data.assignedUserId || this.model.get('assignedUserId');

            if (assignedUserId && this.allowedUsers[assignedUserId]) {
                data.assignedUserName = this.allowedUsers[assignedUserId];
            }

            return data;
        }
    });
});
