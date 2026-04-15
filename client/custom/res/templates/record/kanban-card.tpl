<div class="kanban-card {{cardClass}}" data-id="{{id}}">
    {{! Card Header - Deal/Lead Name with Priority Badge }}
    <div class="card-header">
        {{#if priorityLabel}}
        <span class="priority-badge {{priorityClass}}">{{priorityLabel}}</span>
        {{/if}}
        <h4 class="card-title">{{name}}</h4>
    </div>

    {{! Card Body - Contact & Value }}
    <div class="card-body">
        {{#if accountName}}
        <div class="contact-name">{{accountName}}</div>
        {{/if}}
        
        {{#if formattedValue}}
        <div class="deal-value">{{formattedValue}}</div>
        {{/if}}
    </div>

    {{! Strategic Tags }}
    {{#if source}}
    <div class="card-tags">
        {{#if source}}
        <span class="tag tag-source">{{source}}</span>
        {{/if}}
        
        {{#if insuranceInterest}}
        <span class="tag tag-category">{{insuranceInterest}}</span>
        {{/if}}
    </div>
    {{/if}}

    {{! Card Footer - Due Date & Owner }}
    <div class="card-footer">
        {{#if dueDateLabel}}
        <div class="due-date {{dueDateClass}}">
            📅 {{dueDateLabel}}
        </div>
        {{/if}}
        
        <div class="owner-avatar" title="{{assignedUser}}">
            {{ownerInitials}}
        </div>
    </div>

    {{! Health Indicators }}
    <div class="health-indicators">
        {{#if stagnationDays}}
        <div class="stagnation-timer {{stagnationClass}}">
            ⏱️ {{stagnationDays}}d in stage
        </div>
        {{/if}}
        
        {{#if email}}
        <div class="activity-counter">
            📧 Activity logged
        </div>
        {{/if}}
    </div>
</div>
