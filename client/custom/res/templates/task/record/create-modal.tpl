{{#if contextLabel}}
<div class="task-context-banner" style="background: #fff3e0; border: 1px solid #ffcc80; border-radius: 6px; padding: 10px 16px; margin-bottom: 16px; font-size: 13px; color: #e65100;">
    <i class="fas fa-info-circle"></i>&nbsp; {{contextLabel}}
</div>
{{/if}}

<div class="row">
    <div class="cell col-sm-12 form-group">
        <label class="control-label">Task Title <span class="required">*</span></label>
        <div class="field" data-name="name">{{{name}}}</div>
    </div>
</div>
<div class="row">
    <div class="cell col-sm-6 form-group">
        <label class="control-label">Assigned To <span class="required">*</span></label>
        <div class="field" data-name="assignedUser">{{{assignedUser}}}</div>
    </div>
    <div class="cell col-sm-6 form-group">
        <label class="control-label">Due Date <span class="required">*</span></label>
        <div class="field" data-name="dateEnd">{{{dateEnd}}}</div>
    </div>
</div>
<div class="row">
    <div class="cell col-sm-6 form-group">
        <label class="control-label">Priority</label>
        <div class="field" data-name="priority">{{{priority}}}</div>
    </div>
</div>
<div class="row">
    <div class="cell col-sm-12 form-group">
        <label class="control-label">Notes</label>
        <div class="field" data-name="description">{{{description}}}</div>
    </div>
</div>
