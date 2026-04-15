<div class="kanban-card {{cardClass}}" data-id="{{id}}">
    {{! Card Header - Deal Name with Priority Badge }}
    <div class="card-header">
        {{#if priorityLabel}}
        <span class="priority-badge {{priorityClass}}">{{priorityLabel}}</span>
        {{/if}}
        <h4 class="card-title">{{name}}</h4>
    </div>

    {{! Card Body - Account & Value }}
    <div class="card-body">
        {{#if accountName}}
        <div class="contact-name">{{accountName}}</div>
        {{/if}}
        
        {{#if formattedValue}}
        <div class="deal-value">{{formattedValue}}</div>
        {{/if}}
    </div>

    {{! Strategic Tags }}
    <div class="card-tags">
        {{#if lineOfBusiness}}
        <span class="tag tag-category">{{lineOfBusiness}}</span>
        {{/if}}
        
        {{#if businessType}}
        <span class="tag tag-source">{{businessType}}</span>
        {{/if}}
    </div>

    {{! Card Footer - Close Date & Owner }}
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
    </div>
</div>
