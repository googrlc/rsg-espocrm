define('custom:views/task/fields/assigned-user', ['views/fields/link'], function (Dep) {

    return Dep.extend({

        allowedUsers: {
            '69bdf81552aaa': 'Gretchen Coates',
            '69bdad92458da2204': 'Lamar Coates'
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
