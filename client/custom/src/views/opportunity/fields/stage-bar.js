define('custom:views/opportunity/fields/stage-bar', ['views/fields/enum'], function (EnumFieldView) {

    var PIPELINE_STAGES = [
        'Discovery',
        'Quoting',
        'Markets Out / Shopping',
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
                data.stages = this.getStagesData();
                data.isWon = WON_STAGES.indexOf(current) !== -1;
                data.isLost = LOST_STAGES.indexOf(current) !== -1;
            }

            return data;
        },

        getStagesData: function () {
            var currentStage = this.model.get(this.name);

            var stages = PIPELINE_STAGES;

            var currentIndex = stages.indexOf(currentStage);
            var probabilityMap = this.model.getFieldParam(this.name, 'probabilityMap') || {};
            var result = [];

            stages.forEach(function (stage, index) {
                var isWon = WON_STAGES.indexOf(stage) !== -1;
                var isLost = LOST_STAGES.indexOf(stage) !== -1;
                var isTerminal = isWon || isLost;
                var stageClass = 'stage-pending';

                if (stage === currentStage) {
                    if (isWon) {
                        stageClass = 'stage-won';
                    } else if (isLost) {
                        stageClass = 'stage-lost';
                    } else {
                        stageClass = 'stage-current';
                    }
                } else if (isTerminal) {
                    stageClass = 'stage-terminal';
                } else if (WON_STAGES.indexOf(currentStage) !== -1 ||
                           LOST_STAGES.indexOf(currentStage) !== -1) {
                    stageClass = 'stage-completed';
                } else if (index < currentIndex) {
                    stageClass = 'stage-completed';
                }

                result.push({
                    name: stage,
                    label: stage,
                    stageClass: stageClass,
                    probability: probabilityMap[stage] || 0,
                    isCurrent: stage === currentStage,
                    isTerminal: isTerminal
                });
            }, this);

            return result;
        },

        afterRender: function () {
            EnumFieldView.prototype.afterRender.call(this);

            if (this.mode === 'detail') {
                this.$el.find('.stage-step').on('click', function (e) {
                    var stage = $(e.currentTarget).data('stage');
                    if (stage) {
                        this.model.set(this.name, stage);
                        this.model.save({patch: true});
                    }
                }.bind(this));
            }
        }
    });
});
