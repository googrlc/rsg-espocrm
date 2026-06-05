/************************************************************************
 * RSG "Complete Task" modal
 *
 * Opens from any Task surface (detail, detail-small, list/panel rows,
 * Kanban card). Requires a completion note, marks the task Completed,
 * and posts the note to the task's Stream — without leaving the page.
 *
 * Opened with: { model: <Task model> }
 * Triggers:    'completed' (the Task model) once the status + note are saved.
 ************************************************************************/

define('custom:views/modals/task-complete', ['views/modal'], function (Dep) {

    return Dep.extend({

        backdrop: true,

        cssName: 'task-complete-modal',

        shortcutKeys: {
            'Control+Enter': 'complete'
        },

        templateContent: `
            <div class="task-complete-body">
                <p class="task-complete-intro">
                    Mark <strong>{{taskName}}</strong> as <span class="text-success">Completed</span>
                    and log a note to its stream.
                </p>
                <div class="form-group">
                    <label class="control-label" for="task-completion-note">
                        Completion Note <span class="text-danger">*</span>
                    </label>
                    <textarea
                        id="task-completion-note"
                        class="form-control task-completion-note"
                        rows="4"
                        placeholder="Describe what was done to complete this task…"
                    ></textarea>
                    <div class="task-complete-error text-danger small" style="display:none; margin-top:6px;">
                        A completion note is required.
                    </div>
                </div>
            </div>
        `,

        setup: function () {
            this.model = this.options.model;

            this.headerText = this.translate('Complete Task', 'labels', 'Task');

            this.buttonList = [
                {
                    name: 'complete',
                    label: this.translate('Complete Task', 'labels', 'Task'),
                    style: 'success',
                    iconClass: 'fas fa-check',
                    disabled: true
                },
                {
                    name: 'cancel',
                    label: this.translate('Cancel')
                }
            ];
        },

        data: function () {
            return {
                taskName: this.model.get('name') || this.translate('Task', 'scopeNames')
            };
        },

        afterRender: function () {
            this.$note = this.$el.find('.task-completion-note');
            this.$error = this.$el.find('.task-complete-error');

            this.$note.on('input', this.controlCompleteButton.bind(this));

            setTimeout(function () {
                if (this.$note) {
                    this.$note.focus();
                }
            }.bind(this), 50);
        },

        getNote: function () {
            return this.$note && this.$note.val() ? this.$note.val().trim() : '';
        },

        controlCompleteButton: function () {
            if (this.getNote() !== '') {
                this.$error.hide();
                this.enableButton('complete');
            } else {
                this.disableButton('complete');
            }
        },

        actionComplete: function () {
            var note = this.getNote();

            if (note === '') {
                this.$error.show();
                this.$note.focus();
                return;
            }

            var id = this.model.id;

            this.disableButton('complete');
            Espo.Ui.notify(this.translate('saving', 'messages'));

            this.model
                .save({ status: 'Completed' }, { patch: true })
                .then(function () {
                    return Espo.Ajax.postRequest('Note', {
                        type: 'Post',
                        parentType: this.model.entityType,
                        parentId: id,
                        post: note
                    });
                }.bind(this))
                .then(function () {
                    Espo.Ui.success(this.translate('Task completed', 'messages', 'Task'));
                    this.trigger('completed', this.model);
                    this.close();
                }.bind(this))
                .catch(function () {
                    this.enableButton('complete');
                }.bind(this));
        }
    });
});
