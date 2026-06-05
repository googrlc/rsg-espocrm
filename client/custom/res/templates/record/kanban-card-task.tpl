<div class="kanban-card task-kanban-card {{cardClass}}" data-id="{{id}}" data-state="closed">
    <div class="task-card__header">
        <span class="task-card__priority-dot {{priorityDotClass}}" title="{{priorityTitle}}" aria-hidden="true"></span>
        <h4 class="task-card__title">
            <span class="task-card__title-text">{{name}}</span>
        </h4>
    </div>

    <dl class="task-card__fields">
        {{#if accountName}}
        <div class="task-card__field">
            <dt class="task-card__label">Account</dt>
            <dd class="task-card__value">
                {{#if accountId}}<a href="#Account/view/{{accountId}}" class="task-card__link">{{accountName}}</a>{{else}}{{accountName}}{{/if}}
            </dd>
        </div>
        {{/if}}

        {{#if typeLabel}}
        <div class="task-card__field">
            <dt class="task-card__label">Type</dt>
            <dd class="task-card__value">{{typeLabel}}</dd>
        </div>
        {{/if}}

        <div class="task-card__field">
            <dt class="task-card__label">Assignee</dt>
            <dd class="task-card__value">{{assignedUserName}}</dd>
        </div>

        {{#if dueDateLabel}}
        <div class="task-card__field">
            <dt class="task-card__label">Due</dt>
            <dd class="task-card__value {{dueDateClass}}">{{dueDateLabel}}</dd>
        </div>
        {{/if}}
    </dl>

    <div class="task-card__actions" role="group" aria-label="Task actions">
        <button type="button" class="task-card__btn task-card__btn--open" data-action="openTask" aria-disabled="true" tabindex="-1" title="Open full task detail">
            <span class="fas fa-eye" aria-hidden="true"></span> Open
        </button>
        {{#if canComplete}}
        <button type="button" class="task-card__btn task-card__btn--complete" data-action="complete" aria-disabled="true" tabindex="-1" title="Mark this task complete">
            <span class="fas fa-check" aria-hidden="true"></span> Complete
        </button>
        {{/if}}
        <button type="button" class="task-card__btn task-card__btn--edit" data-action="editTask" aria-disabled="true" tabindex="-1" title="Edit this task">
            <span class="fas fa-pen" aria-hidden="true"></span> Edit
        </button>
        <button type="button" class="task-card__btn task-card__btn--remove" data-action="quickRemove" aria-disabled="true" tabindex="-1" title="Remove this task from the board">
            <span class="fas fa-times" aria-hidden="true"></span> Remove
        </button>
    </div>

    <div class="task-card__footer">
        <button type="button" class="task-card__entry-btn" data-action="toggleExpand" aria-label="Expand task for {{name}}" aria-expanded="false" title="Expand to edit or remove">
            <span class="task-card__entry-glyph" aria-hidden="true">&#8594;</span>
        </button>
    </div>

    <span class="task-card__sr sr-only" aria-live="polite" aria-atomic="true"></span>
</div>
