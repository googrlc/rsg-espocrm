define('custom:views/task/kanban-card', ['views/record/kanban'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-task',

        // Ephemeral, per-render UI state. Reset to collapsed on every re-render.
        isExpanded: false,

        data: function () {
            var data = Dep.prototype.data.call(this);
            var account = this.resolveAccount();
            var dueDate = this.buildDueDate(this.model.get('dateEnd') || this.model.get('dateEndDate'));
            var priority = this.buildPriority(this.model.get('priority'));
            var assignedUserName = this.model.get('assignedUserName') || 'Unassigned';

            var assigneeId = this.model.get('assignedUserId') || '';
            var assigneeClass = 'task-assignee--unassigned';
            var assigneeInitials = '?';

            if (assigneeId === '69bdf81552aaa') {
                assigneeClass = 'task-assignee--gretchen';
                assigneeInitials = 'GC';
            } else if (assigneeId === '69bdad92458da2204') {
                assigneeClass = 'task-assignee--lamar';
                assigneeInitials = 'LC';
            } else if (assigneeId === '6a3ae3d951b79b796') {
                assigneeClass = 'task-assignee--general';
                assigneeInitials = 'GQ';
            }

            return Object.assign({}, data, {
                accountId: account.id,
                accountName: account.name,
                typeLabel: this.translateOption(this.model.get('taskType'), 'taskType'),
                priorityDotClass: priority.dotClass,
                priorityTitle: priority.title,
                dueDateLabel: dueDate.label,
                dueDateClass: dueDate.className,
                assignedUserName: assignedUserName,
                assigneeClass: assigneeClass,
                assigneeInitials: assigneeInitials,
                cardClass: dueDate.cardClass,
                canComplete: ['Completed', 'Cancelled', 'Archived'].indexOf(this.model.get('status')) === -1
            });
        },

        resolveAccount: function () {
            var id = this.model.get('accountId');
            var name = this.model.get('accountName');

            if (!id && this.model.get('parentType') === 'Account') {
                id = this.model.get('parentId');
                name = this.model.get('parentName');
            }

            return {id: id, name: name};
        },

        translateOption: function (value, field) {
            if (!value) {
                return '';
            }

            return this.getLanguage().translateOption(value, field, 'Task') || value;
        },

        buildPriority: function (priority) {
            var value = priority || 'Normal';
            var normalized = String(value).toLowerCase().replace(/\s+/g, '-');

            return {
                title: 'Priority: ' + value,
                dotClass: 'task-card__priority-dot--' + normalized
            };
        },

        buildDueDate: function (dateValue) {
            if (!dateValue) {
                return {label: '', className: '', cardClass: ''};
            }

            var today = new Date();
            today.setHours(0, 0, 0, 0);

            var due = new Date(dateValue);
            due.setHours(0, 0, 0, 0);

            var diffDays = Math.floor((due - today) / 86400000);

            if (diffDays < 0) {
                return {
                    label: 'Overdue',
                    className: 'task-due-overdue',
                    cardClass: 'task-card-overdue'
                };
            }

            if (diffDays === 0) {
                return {
                    label: 'Today',
                    className: 'task-due-today',
                    cardClass: 'task-card-due-today'
                };
            }

            return {
                label: due.toLocaleDateString('en-US', {month: '2-digit', day: '2-digit', year: 'numeric'}),
                className: 'task-due-upcoming',
                cardClass: ''
            };
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.$card = this.$el.find('.task-kanban-card');

            if (!this.$card.length) {
                // Some EspoCRM builds set the card element as the view root itself.
                this.$card = this.$el;
            }

            this.$toggleBtn = this.$card.find('.task-card__entry-btn');
            this.$toggleGlyph = this.$card.find('.task-card__entry-glyph');
            this.$srRegion = this.$card.find('.task-card__sr');

            // Re-render always starts collapsed.
            this.isExpanded = false;
            this.applyExpandedState(false, false);

            // Click on the title opens the task directly (bypasses data-action delegation).
            // Stop propagation on mousedown too so the kanban drag handler doesn't hijack the click.
            this.$card.find('.task-card__title-link')
                .on('mousedown.taskOpen touchstart.taskOpen', function (e) {
                    e.stopPropagation();
                })
                .on('click.taskOpen', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.getRouter().navigate('#Task/view/' + this.model.id, {trigger: true});
                }.bind(this));

            // Escape collapses an open card; keep drag from starting on the toggle.
            this.$card.on('keydown.taskCard', this.onCardKeyDown.bind(this));
            this.$toggleBtn.on('mousedown.taskCard touchstart.taskCard', function (e) {
                e.stopPropagation();
            });
        },

        onRemove: function () {
            if (this.$card) {
                this.$card.off('.taskCard');
            }

            if (this.$toggleBtn) {
                this.$toggleBtn.off('.taskCard');
            }

            this.$card.find('.task-card__title-link').off('.taskOpen');

            Dep.prototype.onRemove.call(this);
        },

        onCardKeyDown: function (e) {
            if (e.key === 'Escape' && this.isExpanded) {
                this.applyExpandedState(false, true);
            }
        },

        actionToggleExpand: function () {
            this.applyExpandedState(!this.isExpanded, true);
        },

        applyExpandedState: function (expanded, announce) {
            this.isExpanded = expanded;

            if (!this.$card || !this.$card.length) {
                return;
            }

            var name = this.model.get('name') || 'task';

            this.$card.attr('data-state', expanded ? 'open' : 'closed');
            this.$card.toggleClass('task-card--open', expanded);

            if (this.$toggleGlyph) {
                this.$toggleGlyph.text(expanded ? '↓' : '→');
            }

            if (this.$toggleBtn) {
                this.$toggleBtn
                    .attr('aria-expanded', expanded ? 'true' : 'false')
                    .attr('aria-label', (expanded ? 'Collapse' : 'Expand') + ' task for ' + name)
                    .attr('title', expanded ? 'Collapse card' : 'Expand to edit or remove');
            }

            this.$card.find('.task-card__actions button')
                .attr('aria-disabled', expanded ? 'false' : 'true')
                .attr('tabindex', expanded ? '0' : '-1');

            if (announce && this.$srRegion) {
                this.$srRegion.text(expanded ? 'Task expanded.' : 'Task collapsed.');
            }

            if (!expanded && announce && this.$toggleBtn) {
                this.$toggleBtn.focus();
            }
        },

        actionOpenTask: function () {
            this.getRouter().navigate('#Task/view/' + this.model.id, {trigger: true});
        },

        actionEditTask: function () {
            // Gated: Edit is only reachable while the card is expanded.
            if (!this.isExpanded) {
                return;
            }

            this.getRouter().navigate('#Task/edit/' + this.model.id, {trigger: true});
        },

        actionComplete: function () {
            if (!this.isExpanded) {
                return;
            }

            if (['Completed', 'Cancelled', 'Archived'].indexOf(this.model.get('status')) !== -1) {
                return;
            }

            var self = this;

            this.createView('taskCompleteModal', 'custom:views/modals/task-complete', {
                model: this.model
            }, function (modal) {
                modal.render();

                self.listenToOnce(modal, 'completed', function () {
                    // The card is no longer in an open column — drop it from view.
                    self.remove();
                });
            });
        },

        actionQuickRemove: function () {
            if (!this.isExpanded) {
                return;
            }

            if (!confirm('Delete this task?')) {
                return;
            }

            this.model.destroy({
                wait: true,
                success: function () {
                    this.remove();
                }.bind(this)
            });
        }
    });
});
