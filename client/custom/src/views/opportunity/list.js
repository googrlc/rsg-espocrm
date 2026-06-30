define('custom:views/opportunity/list', ['views/list'], function (Dep) {

    return Dep.extend({

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.bindKanbanCardOpen();
        },

        onRemove: function () {
            this.$el.off('.rsgOpportunityKanbanOpen');
            Dep.prototype.onRemove.call(this);
        },

        bindKanbanCardOpen: function () {
            var selector = '.list-kanban .group-column-list > .item > .panel';

            this.$el.off('.rsgOpportunityKanbanOpen');

            this.$el.find(selector)
                .attr('role', 'link')
                .attr('tabindex', '0');

            this.$el.on('mousedown.rsgOpportunityKanbanOpen touchstart.rsgOpportunityKanbanOpen', selector, function (e) {
                var event = e.originalEvent || e;
                var point = event.touches && event.touches.length ? event.touches[0] : event;

                $(e.currentTarget).data('rsgOpenStartPoint', {
                    x: point.clientX || 0,
                    y: point.clientY || 0
                });
            });

            this.$el.on('click.rsgOpportunityKanbanOpen', selector, function (e) {
                this.openKanbanCard(e);
            }.bind(this));

            this.$el.on('keydown.rsgOpportunityKanbanOpen', selector, function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    this.openKanbanCard(e);
                }
            }.bind(this));
        },

        openKanbanCard: function (e) {
            if (this.shouldIgnoreKanbanOpen(e)) {
                return;
            }

            var $card = $(e.currentTarget);
            var id = $card.closest('.item').data('id') || $card.find('.dropdown-menu[data-id]').data('id');

            if (!id) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            this.getRouter().navigate('#Opportunity/view/' + id, {trigger: true});
        },

        shouldIgnoreKanbanOpen: function (e) {
            var $target = $(e.target);

            if ($target.closest('a, button, input, select, textarea, [data-action], .item-menu-container, .list-row-buttons, .dropdown-menu').length) {
                return true;
            }

            if (window.getSelection && String(window.getSelection())) {
                return true;
            }

            var point = $(e.currentTarget).data('rsgOpenStartPoint');

            if (!point) {
                return false;
            }

            var event = e.originalEvent || e;
            var dx = Math.abs((event.clientX || 0) - point.x);
            var dy = Math.abs((event.clientY || 0) - point.y);

            return dx > 5 || dy > 5;
        }
    });
});
