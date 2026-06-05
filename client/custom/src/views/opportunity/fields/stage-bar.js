define('custom:views/opportunity/fields/stage-bar', ['views/fields/enum'], function (EnumFieldView) {

    var PIPELINE_STAGES = [
        'Discovery',
        'Quoting',
        'Markets Out / Shopping',
        'Quotes Complete',
        'Proposal Presented',
        'Negotiation',
        'Closed Won',
        'Closed Lost'
    ];

    var WON_STAGES = ['Closed Won'];
    var LOST_STAGES = ['Closed Lost'];

    return EnumFieldView.extend({

        detailTemplate: 'custom:opportunity/fields/stage-bar/detail',
        listTemplate: 'custom:opportunity/fields/stage-bar/list',
        editTemplate: 'fields/enum/edit',

        data: function () {
            var data = EnumFieldView.prototype.data.call(this);
            var current = this.model.get(this.name);

            if (this.mode === 'detail') {
                data.currentStage = current || '';
                data.currentLabel = this.getLanguage().translateOption(current, this.name, this.model.entityType) || current || 'None';
                data.currentProbability = (this.model.getFieldParam(this.name, 'probabilityMap') || {})[current];
                data.stageClass = this.getStageClass(current);
                data.isWon = WON_STAGES.indexOf(current) !== -1;
                data.isLost = LOST_STAGES.indexOf(current) !== -1;
            }

            return data;
        },

        getStageClass: function (stage) {
            if (WON_STAGES.indexOf(stage) !== -1) {
                return 'stage-won';
            }

            if (LOST_STAGES.indexOf(stage) !== -1) {
                return 'stage-lost';
            }

            if (stage) {
                return 'stage-current';
            }

            return 'stage-pending';
        },

        afterRender: function () {
            EnumFieldView.prototype.afterRender.call(this);
        }
    });
});
