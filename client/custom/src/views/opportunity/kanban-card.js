Espo.define('custom:views/opportunity/kanban-card', ['custom:views/core/kanban-card'], function (Dep) {

    return Dep.extend({

        template: 'custom:record/kanban-card-opportunity',

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.$card = this.$el.find('.opportunity-kanban-card, .panel').first();

            if (!this.$card.length) {
                this.$card = this.$el;
            }

            this.$card
                .attr('role', 'link')
                .attr('tabindex', '0')
                .attr('aria-label', 'Open opportunity ' + (this.model.get('name') || 'record'))
                .off('.opportunityOpen')
                .on('mousedown.opportunityOpen touchstart.opportunityOpen', function (e) {
                    var event = e.originalEvent || e;
                    var point = event.touches && event.touches.length ? event.touches[0] : event;

                    this.openStartPoint = {
                        x: point.clientX || 0,
                        y: point.clientY || 0
                    };
                }.bind(this))
                .on('click.opportunityOpen', function (e) {
                    this.openRecordFromCard(e);
                }.bind(this))
                .on('keydown.opportunityOpen', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        this.openRecordFromCard(e);
                    }
                }.bind(this));
        },

        onRemove: function () {
            if (this.$card) {
                this.$card.off('.opportunityOpen');
            }

            Dep.prototype.onRemove.call(this);
        },

        data: function () {
            const data = Dep.prototype.data.call(this);
            const stagnationDays = this.buildStagnationDays();
            const assignedUserName = this.model.get('assignedUserName');
            const estimatedPremium = this.model.get('estimatedPremium');
            const formattedValue = this.buildFormattedValue('estimatedPremium', 'estimatedPremiumCurrency');
            const { cardClass, stagnationClass } = this.buildCardClasses(estimatedPremium, stagnationDays);

            // Close date color coding
            const closeDate = this.model.get('closeDate');
            let dueDateClass = 'due-date-upcoming';
            let dueDateLabel = '';
            if (closeDate) {
                const now = new Date();
                const due = new Date(closeDate);
                const diffDays = Math.floor((due - now) / (1000 * 60 * 60 * 24));

                if (diffDays < 0) {
                    dueDateClass = 'due-date-overdue';
                    dueDateLabel = `Overdue (${Math.abs(diffDays)}d)`;
                } else if (diffDays === 0) {
                    dueDateClass = 'due-date-today';
                    dueDateLabel = 'Today';
                } else if (diffDays <= 7) {
                    dueDateLabel = `${diffDays}d`;
                } else {
                    dueDateLabel = due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }

            return Object.assign({}, data, {
                dueDateClass: dueDateClass,
                dueDateLabel: dueDateLabel,
                assignedUserName: assignedUserName,
                ownerDisplay: this.buildOwnerDisplay(assignedUserName),
                lineOfBusiness: this.model.get('lineOfBusiness'),
                businessType: this.model.get('businessType'),
                formattedValue: formattedValue,
                cardClass: cardClass,
                stagnationDays: stagnationDays,
                stagnationClass: stagnationClass
            });
        },

        openRecordFromCard: function (e) {
            if (this.shouldIgnoreCardOpen(e)) {
                return;
            }

            e.preventDefault();

            if (e.stopPropagation) {
                e.stopPropagation();
            }

            this.getRouter().navigate('#Opportunity/view/' + this.model.id, {trigger: true});
        },

        shouldIgnoreCardOpen: function (e) {
            var $target = $(e.target);

            if ($target.closest('a, button, input, select, textarea, [data-action], .item-menu-container, .list-row-buttons, .dropdown-menu').length) {
                return true;
            }

            if (window.getSelection && String(window.getSelection())) {
                return true;
            }

            if (!this.openStartPoint) {
                return false;
            }

            var event = e.originalEvent || e;
            var dx = Math.abs((event.clientX || 0) - this.openStartPoint.x);
            var dy = Math.abs((event.clientY || 0) - this.openStartPoint.y);

            return dx > 5 || dy > 5;
        },

        buildOwnerDisplay: function (assignedUserName) {
            if (!assignedUserName) {
                return '';
            }

            return assignedUserName.split(/\s+/).filter(Boolean)[0] || assignedUserName;
        }
    });
});
