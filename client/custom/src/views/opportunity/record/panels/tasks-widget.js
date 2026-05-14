define('custom:views/opportunity/record/panels/tasks-widget', ['views/record/panels/side'], function (SidePanelView) {

    return SidePanelView.extend({

        templateContent: '<div class="opp-tasks-widget">' +
            '<div class="tasks-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
            '<span class="task-count text-muted" style="font-size: 12px;"></span>' +
            '<a href="javascript:" class="action btn btn-warning btn-xs" data-action="createTask"><i class="fas fa-plus"></i> Task</a>' +
            '</div>' +
            '<div class="tasks-list"></div>' +
            '</div>',

        setup: function () {
            SidePanelView.prototype.setup.call(this);
        },

        afterRender: function () {
            SidePanelView.prototype.afterRender.call(this);
            this.loadTasks();
        },

        loadTasks: function () {
            var $list = this.$el.find('.tasks-list');
            var $count = this.$el.find('.task-count');
            $list.empty();

            this.getCollectionFactory().create('Task', function (collection) {
                collection.where = [
                    {
                        type: 'equals',
                        attribute: 'parentId',
                        value: this.model.get('id')
                    },
                    {
                        type: 'equals',
                        attribute: 'parentType',
                        value: 'Opportunity'
                    }
                ];
                collection.maxSize = 10;
                collection.orderBy = 'dateEnd';
                collection.order = 'asc';

                collection.fetch().then(function () {
                    var openCount = 0;

                    collection.forEach(function (task) {
                        var isComplete = task.get('status') === 'Completed' || task.get('status') === 'Cancelled';
                        if (!isComplete) openCount++;

                        var dueDate = task.get('dateEnd') ? this.getDateTime().toDisplayDate(task.get('dateEnd')) : '';
                        var isOverdue = task.get('dateEnd') && new Date(task.get('dateEnd')) < new Date() && !isComplete;

                        var html = '<div class="task-row" style="display: flex; align-items: center; padding: 4px 0; border-bottom: 1px solid #f0f0f0; font-size: 12px;' + (isComplete ? ' opacity: 0.5;' : '') + '">' +
                            '<input type="checkbox" class="task-checkbox" data-id="' + task.get('id') + '"' + (isComplete ? ' checked' : '') + ' style="margin-right: 8px;">' +
                            '<a href="#Task/view/' + task.get('id') + '" style="flex: 1; color: #333; text-decoration: none;' + (isComplete ? ' text-decoration: line-through;' : '') + '">' + (task.get('name') || 'Untitled') + '</a>' +
                            (dueDate ? '<span style="color: ' + (isOverdue ? '#c62828; font-weight: 700;' : '#999;') + ' font-size: 11px; margin-left: 8px;">' + dueDate + '</span>' : '') +
                            '</div>';

                        $list.append(html);
                    }, this);

                    $count.text(openCount + ' open');

                    if (collection.length === 0) {
                        $list.html('<div class="text-muted" style="font-size: 12px;">No tasks</div>');
                    }

                    this.$el.find('.task-checkbox').on('change', function (e) {
                        var taskId = $(e.target).data('id');
                        var checked = $(e.target).is(':checked');
                        this.toggleTaskComplete(taskId, checked);
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        },

        toggleTaskComplete: function (taskId, complete) {
            this.getModelFactory().create('Task', function (model) {
                model.set('id', taskId);
                model.set('status', complete ? 'Completed' : 'In Progress');
                model.save({patch: true}).then(function () {
                    this.loadTasks();
                }.bind(this));
            }.bind(this));
        },

        actionCreateTask: function () {
            var accountId = this.model.get('accountId');
            var accountName = this.model.get('accountName');

            this.createView('taskModal', 'custom:views/task/record/create-modal', {
                scope: 'Task',
                sourceType: 'Opportunity',
                accountId: accountId,
                accountName: accountName,
                opportunityId: this.model.get('id'),
                opportunityName: this.model.get('name'),
                contextLabel: 'Opportunity task - ' + (this.model.get('name') || '') + ' - ' + (accountName || '')
            }, function (view) {
                view.render();
                this.listenToOnce(view, 'after:save', function () {
                    this.loadTasks();
                }, this);
            });
        }
    });
});
