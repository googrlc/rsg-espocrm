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
            var current = this.model.get(this.name) || '';
            var probMap = this.model.getFieldParam(this.name, 'probabilityMap') || {};

            if (this.mode === 'detail' || this.mode === 'list') {
                data.currentStage = current;
                data.currentLabel = this.getLanguage().translateOption(current, this.name, this.model.entityType) || current || 'None';
                data.currentProbability = probMap[current] !== undefined ? probMap[current] : '';
                data.stageClass = this.getStageClass(current);
                data.isWon = WON_STAGES.indexOf(current) !== -1;
                data.isLost = LOST_STAGES.indexOf(current) !== -1;

                data.stages = PIPELINE_STAGES.map(function (stageName) {
                    var isCurrent = stageName === current;
                    var isPassed = this.isStagePassed(current, stageName);
                    var isWon = WON_STAGES.indexOf(stageName) !== -1;
                    var isLost = LOST_STAGES.indexOf(stageName) !== -1;

                    return {
                        name: stageName,
                        label: this.getLanguage().translateOption(stageName, this.name, this.model.entityType) || stageName,
                        probability: probMap[stageName] !== undefined ? probMap[stageName] : '',
                        stageClass: isWon ? 'stage-won' : (isLost ? 'stage-lost' : (isCurrent ? 'stage-current' : (isPassed ? 'stage-completed' : 'stage-pending'))),
                        isCurrent: isCurrent,
                        isPassed: isPassed
                    };
                }, this);
            }

            return data;
        },

        isStagePassed: function (currentStage, checkStage) {
            if (!currentStage) { return false; }
            var currentIdx = PIPELINE_STAGES.indexOf(currentStage);
            var checkIdx = PIPELINE_STAGES.indexOf(checkStage);
            if (currentIdx === -1 || checkIdx === -1) { return false; }
            // Don't mark won/lost as "passed" for pipeline stages
            if (WON_STAGES.indexOf(checkStage) !== -1 || LOST_STAGES.indexOf(checkStage) !== -1) { return false; }
            return checkIdx < currentIdx;
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
        }
    });
});
