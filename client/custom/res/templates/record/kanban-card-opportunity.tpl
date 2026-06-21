<div class="kanban-card opportunity-kanban-card {{cardClass}}" data-id="{{id}}" title="{{name}}">
    <div class="opportunity-card-topline">
        <div class="opportunity-card-metric">
            <span class="opportunity-card-label">Premium</span>
            <strong class="opportunity-card-premium">{{#if formattedValue}}{{formattedValue}}{{else}}None{{/if}}</strong>
        </div>
        <span class="opportunity-card-arrow" aria-hidden="true">↗️</span>
    </div>

    <div class="opportunity-card-fields">
        {{#if lineOfBusiness}}
        <div class="opportunity-card-field">
            <span class="opportunity-card-label">LOB</span>
            <span class="opportunity-card-value">{{lineOfBusiness}}</span>
        </div>
        {{/if}}

        {{#if businessType}}
        <div class="opportunity-card-field">
            <span class="opportunity-card-label">Deal</span>
            <span class="opportunity-card-value">{{businessType}}</span>
        </div>
        {{/if}}

        {{#if ownerDisplay}}
        <div class="opportunity-card-field">
            <span class="opportunity-card-label">Owner</span>
            <span class="opportunity-card-value" title="{{assignedUserName}}">{{ownerDisplay}}</span>
        </div>
        {{/if}}

        {{#if dueDateLabel}}
        <div class="opportunity-card-field">
            <span class="opportunity-card-label">Close</span>
            <span class="opportunity-card-value {{dueDateClass}}">{{dueDateLabel}}</span>
        </div>
        {{/if}}
    </div>

    {{#if stagnationDays}}
    <div class="opportunity-card-stage-age {{stagnationClass}}">
        <span class="opportunity-card-label">Stage age</span>
        <span>{{stagnationDays}}d</span>
    </div>
    {{/if}}
</div>
