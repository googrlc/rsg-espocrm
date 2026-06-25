<div class="stage-bar-container">
    {{#if currentStage}}
    <div class="stage-current-display {{stageClass}}">
        {{currentLabel}}
        {{#if currentProbability}}<span class="stage-current-probability">{{currentProbability}}%</span>{{/if}}
    </div>
    {{/if}}
    <div class="stage-bar">
        {{#each stages}}
        <div class="stage-step {{stageClass}}" data-stage="{{name}}" title="{{label}}{{#if probability}} ({{probability}}%){{/if}}">
            <span class="stage-label">{{label}}</span>
        </div>
        {{/each}}
    </div>
</div>
