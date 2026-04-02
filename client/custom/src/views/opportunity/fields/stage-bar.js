define('custom:views/opportunity/fields/stage-bar', ['views/fields/enum'], function (EnumFieldView) {

    return EnumFieldView.extend({

        detailTemplate: 'custom:opportunity/fields/stage-bar/detail',
        listTemplate: 'custom:opportunity/fields/stage-bar/list',

        data: function () {
            var data = EnumFieldView.prototype.data.call(this);
            data.stages = this.getStagesData();
            data.isWon = this.model.get(this.name) === 'Closed Won';
            data.isLost = this.model.get(this.name) === 'Closed Lost';
            return data;
        },

        getStagesData: function () {
            var currentStage = this.model.get(this.name);
            var lob = this.model.get('lineOfBusiness') || '';
            var stages;

            var personalLobs = [
                'Personal Auto', 'Homeowners', 'Renters', 'Condo',
                'Dwelling Fire', 'Motorcycle', 'Boat', 'RV',
                'Life', 'Health', 'Medicare'
            ];

            if (personalLobs.indexOf(lob) !== -1) {
                stages = ['Discovery', 'Quoting', 'Proposal Presented', 'Closed Won', 'Closed Lost'];
            } else {
                stages = ['Discovery', 'Quoting', 'Proposal Presented', 'Negotiation', 'Closed Won', 'Closed Lost'];
            }

            var currentIndex = stages.indexOf(currentStage);
            var result = [];

            stages.forEach(function (stage, index) {
                var stageClass = 'stage-pending';

                if (stage === currentStage) {
                    if (stage === 'Closed Won') {
                        stageClass = 'stage-won';
                    } else if (stage === 'Closed Lost') {
                        stageClass = 'stage-lost';
                    } else {
                        stageClass = 'stage-current';
                    }
                } else if (stage === 'Closed Lost' || stage === 'Closed Won') {
                    stageClass = 'stage-terminal';
                } else if (currentStage === 'Closed Won' || currentStage === 'Closed Lost') {
                    stageClass = 'stage-completed';
                } else if (index < currentIndex) {
                    stageClass = 'stage-completed';
                }

                var probabilityMap = this.model.getFieldParam(this.name, 'probabilityMap') || {};

                result.push({
                    name: stage,
                    label: stage,
                    stageClass: stageClass,
                    probability: probabilityMap[stage] || 0,
                    isCurrent: stage === currentStage,
                    isTerminal: stage === 'Closed Lost' || stage === 'Closed Won'
                });
            }, this);

            return result;
        },

        afterRender: function () {
            EnumFieldView.prototype.afterRender.call(this);

            if (this.isDetailMode()) {
                this.$el.find('.stage-step').on('click', function (e) {
                    var stage = $(e.currentTarget).data('stage');
                    if (stage) {
                        this.model.set(this.name, stage);
                        this.model.save({patch: true});
                    }
                }.bind(this));
            }
        },

        isDetailMode: function () {
            return this.mode === 'detail' || this.mode === 'edit';
        }
    });
});
