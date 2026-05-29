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

    var STAGE_COLORS = {
        'Discovery':              {bg: '#42a5f5', color: '#fff'},
        'Quoting':                {bg: '#7e57c2', color: '#fff'},
        'Markets Out / Shopping': {bg: '#ff9800', color: '#fff'},
        'Proposal Presented':     {bg: '#26a69a', color: '#fff'},
        'Negotiation':            {bg: '#5c6bc0', color: '#fff'},
        'Closed Won':             {bg: '#2e7d32', color: '#fff'},
        'Closed Lost':            {bg: '#c62828', color: '#fff'}
    };

    var WON_STAGES = ['Closed Won'];
    var LOST_STAGES = ['Closed Lost'];

    return EnumFieldView.extend({

        listTemplate: 'custom:opportunity/fields/stage-bar/list',
        editTemplate: 'fields/enum/edit',

        data: function () {
            var data = EnumFieldView.prototype.data.call(this);
            var current = this.model.get(this.name);

            if (this.mode === 'list' || this.mode === 'listLink') {
                var styleMap = this.model.getFieldParam(this.name, 'style') || {};
                data.style = styleMap[current] || 'default';
                if (!data.translatedValue && current) {
                    var translatedOptions = this.translatedOptions || {};
                    data.translatedValue = translatedOptions[current] || current;
                }
            }

            return data;
        },

        getStageState: function (stage, index, currentStage, currentIndex) {
            var isWon = WON_STAGES.indexOf(stage) !== -1;
            var isLost = LOST_STAGES.indexOf(stage) !== -1;
            var isTerminal = isWon || isLost;

            if (stage === currentStage) {
                return 'active';
            }
            if (isTerminal) {
                return 'terminal';
            }
            if (WON_STAGES.indexOf(currentStage) !== -1 || LOST_STAGES.indexOf(currentStage) !== -1) {
                return 'completed';
            }
            if (index < currentIndex) {
                return 'completed';
            }
            return 'pending';
        },

        afterRender: function () {
            EnumFieldView.prototype.afterRender.call(this);

            if (this.mode === 'detail') {
                var currentStage = this.model.get(this.name);
                var currentIndex = PIPELINE_STAGES.indexOf(currentStage);
                var probabilityMap = this.model.getFieldParam(this.name, 'probabilityMap') || {};
                var html = '<div class="stage-bar-container"><div class="stage-bar">';

                PIPELINE_STAGES.forEach(function (stage, index) {
                    var state = this.getStageState(stage, index, currentStage, currentIndex);
                    var colors = STAGE_COLORS[stage] || {bg: '#e0e0e0', color: '#757575'};
                    var prob = probabilityMap[stage] || 0;
                    var style = '';
                    var cls = 'stage-step';

                    if (state === 'active') {
                        style = 'background:' + colors.bg + ';color:' + colors.color +
                            ';font-weight:600;box-shadow:0 2px 6px rgba(0,0,0,0.25);';
                        cls += ' stage-active';
                    } else if (state === 'completed') {
                        style = 'background:' + colors.bg + ';color:' + colors.color + ';opacity:0.55;';
                        cls += ' stage-completed';
                    } else if (state === 'terminal') {
                        style = 'background:#f0f0f0;color:#bdbdbd;';
                        cls += ' stage-terminal';
                    } else {
                        style = 'background:#f5f5f5;color:#bdbdbd;';
                        cls += ' stage-pending';
                    }

                    html += '<div class="' + cls + '" ' +
                        'style="' + style + '" ' +
                        'data-stage="' + stage + '" ' +
                        'title="' + stage + ' (' + prob + '%)">' +
                        '<span class="stage-label">' + stage + '</span>' +
                        '<span class="stage-probability">' + prob + '%</span>' +
                        '</div>';
                }, this);

                html += '</div></div>';
                this.$el.html(html);

                this.$el.find('.stage-step').on('click', function (e) {
                    var stageName = $(e.currentTarget).data('stage');
                    if (stageName) {
                        this.model.set(this.name, stageName);
                        this.model.save({patch: true});
                    }
                }.bind(this));
            }
        }
    });
});
