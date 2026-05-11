/**
 * RSG list/global search — cursor stability for the quick text filter.
 *
 * Espo's default focus handler selects the entire text field on every focus, which
 * destroys deliberate caret placement when the input is briefly blurred/refocused.
 * We also debounce input-driven searches so rapid typing does not queue overlapping
 * collection fetches (which can trigger UI churn and refocus side effects).
 *
 * Events map is aligned with EspoCRM stable views/record/search so we do not drop
 * advanced filter / preset / view-mode bindings.
 */
define('custom:views/record/search', ['views/record/search'], function (Search) {
    'use strict';

    var Base = Search.default || Search;
    var TEXT_FILTER_DEBOUNCE_MS = 350;

    return Base.extend({
        events: {
            'keydown input[data-name="textFilter"]': function (e) {
                if (this._rsgTextFilterTimer) {
                    clearTimeout(this._rsgTextFilterTimer);
                    this._rsgTextFilterTimer = null;
                }
                var key = Espo.Utils.getKeyFromKeyEvent(e);
                if (e.key === 'Enter' || key === 'Enter' || key === 'Control+Enter') {
                    this.search();
                    this.hideApplyFiltersButton();
                }
            },
            'focus input[data-name="textFilter"]': function () {},
            'input input[data-name="textFilter"]': function () {
                var self = this;
                if (this._rsgTextFilterTimer) {
                    clearTimeout(this._rsgTextFilterTimer);
                }
                this._rsgTextFilterTimer = setTimeout(function () {
                    self._rsgTextFilterTimer = null;
                    self.search();
                }, TEXT_FILTER_DEBOUNCE_MS);
            },
            'click .advanced-filters-apply-container a[data-action="applyFilters"]': function () {
                this.search();
                this.hideApplyFiltersButton();
                this.$el.find('button.search').focus();
            },
            'click button[data-action="search"]': function () {
                this.search();
                this.hideApplyFiltersButton();
            },
            'click a[data-action="addFilter"]': function (e) {
                var $target = $(e.currentTarget);
                var name = $target.data('name');
                $target.closest('li').addClass('hidden');
                this.addFilter(name);
            },
            'click .advanced-filters a.remove-filter': function (e) {
                var $target = $(e.currentTarget);
                var name = $target.data('name');
                this.removeFilter(name);
            },
            'click button[data-action="reset"]': function () {
                this.resetFilters();
            },
            'click button[data-action="refresh"]': function () {
                this.refresh();
            },
            'click a[data-action="selectPreset"]': function (e) {
                var $target = $(e.currentTarget);
                var presetName = $target.data('name') || null;
                this.selectPreset(presetName);
            },
            'click .dropdown-menu a[data-action="savePreset"]': function () {
                this.createView('savePreset', 'views/modals/save-filters', {}, function (view) {
                    view.render();
                    this.listenToOnce(view, 'save', function (name) {
                        this.savePreset(name);
                        view.close();
                        this.removeFilters();
                        this.createFilters(function () {
                            this.render();
                        }.bind(this));
                    }.bind(this));
                }.bind(this));
            },
            'click .dropdown-menu a[data-action="removePreset"]': function () {
                var id = this.presetName;
                this.confirm(this.translate('confirmation', 'messages'), function () {
                    this.removePreset(id);
                }.bind(this));
            },
            'change .search-row ul.filter-menu input[data-role="boolFilterCheckbox"]': function (e) {
                e.stopPropagation();
                this.search();
                this.manageLabels();
            },
            'click [data-action="switchViewMode"]': function (e) {
                var mode = $(e.currentTarget).data('name');
                if (mode === this.viewMode) {
                    return;
                }
                this.setViewMode(mode, false, true);
            },
            'keyup input.field-filter-quick-search-input': function (e) {
                this.processFieldFilterQuickSearch(e.currentTarget.value);
            },
            'keydown input.field-filter-quick-search-input': function (e) {
                if (e.code === 'Enter') {
                    this.addFirstFieldFilter();
                    return;
                }
                if (e.code === 'Escape') {
                    this.closeAddFieldDropdown();
                }
            }
        },

        setup: function () {
            Base.prototype.setup.call(this);
            this.once('remove', function () {
                if (this._rsgTextFilterTimer) {
                    clearTimeout(this._rsgTextFilterTimer);
                    this._rsgTextFilterTimer = null;
                }
            }, this);
        }
    });
});
