<div class="stage-bar-container">
    <div class="stage-bar">
        {{#each stages}}
        <div class="stage-step {{stageClass}}" data-stage="{{name}}" title="{{label}} ({{probability}}%)">
            <span class="stage-label">{{label}}</span>
            <span class="stage-probability">{{probability}}%</span>
        </div>
        {{/each}}
    </div>
</div>
