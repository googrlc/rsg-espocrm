<div class="kanban-card task-kanban-card {{cardClass}}" data-id="{{id}}">
    <div class="task-card-topline">
        <h4 class="card-title">
            <a href="#Task/view/{{id}}">{{name}}</a>
        </h4>

        <div class="dropdown task-card-actions">
            <button class="btn btn-link btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Task actions">
                <span class="fas fa-ellipsis-h"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right">
                <li><a href="#Task/view/{{id}}">View</a></li>
                <li><a href="#Task/edit/{{id}}">Edit</a></li>
                <li><a href="javascript:" data-action="quickRemove">Delete</a></li>
            </ul>
        </div>
    </div>

    <div class="task-card-meta">
        {{#if accountName}}
        <div class="task-card-account">
            {{#if accountId}}
            <a href="#Account/view/{{accountId}}" class="task-card-account-link">{{accountName}}</a>
            {{else}}
            <span class="task-card-account-link">{{accountName}}</span>
            {{/if}}
        </div>
        {{/if}}

        <div class="task-card-row task-card-scan-row">
            {{#if statusLabel}}
            <span class="task-status-pill {{statusClass}}">{{statusLabel}}</span>
            {{/if}}

            {{#if priorityLabel}}
            <span class="task-priority-pill {{priorityClass}}">{{priorityLabel}}</span>
            {{/if}}

            {{#if dueDateLabel}}
            <span class="task-due-pill {{dueDateClass}}">{{dueDateLabel}}</span>
            {{/if}}
        </div>

        {{#if taskType}}
        <div class="task-card-row task-card-type-row">
            <span class="task-card-label">Type</span>
            <span class="task-card-value">{{taskType}}</span>
        </div>
        {{/if}}
    </div>

    <div class="task-card-assignee" title="{{assignedUserName}}">
        <span class="owner-avatar">{{ownerInitials}}</span>
        <span class="task-card-assignee-name">{{assignedUserName}}</span>
    </div>
</div>
