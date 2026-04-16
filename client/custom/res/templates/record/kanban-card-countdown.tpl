<div class="kanban-card kanban-card-countdown" data-id="{{id}}">
    <div class="card-header">
        <h4 class="card-title">{{name}}</h4>
    </div>

    <div class="card-body">
        {{#if cardSubtitle}}
        <div class="contact-name">{{cardSubtitle}}</div>
        {{/if}}

        {{#if secondaryText}}
        <div class="muted small">{{secondaryText}}</div>
        {{/if}}
    </div>

    {{#if countdownLabel}}
    <div class="card-footer">
        <div class="due-date {{countdownClass}}">
            {{countdownLabel}}
        </div>
    </div>
    {{/if}}
</div>
