/**
 * Selectize Focus Fix for EspoCRM
 *
 * Prevents search/dropdown inputs from losing focus when the view
 * re-renders or other DOM events fire. Patches Selectize's blur
 * handler to ignore programmatic blur events.
 */
(function () {
    'use strict';

    function applyFix() {
        if (typeof $.fn.selectize === 'undefined') {
            return;
        }

        var originalSetup = $.fn.selectize.defaults.setup;

        $.fn.selectize.defaults.setup = function () {
            if (typeof originalSetup === 'function') {
                originalSetup.call(this);
            }

            var self = this;

            // Prevent blur when clicking inside the dropdown or its children.
            this.$dropdown.on('mousedown', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });

            // Re-focus the control input after a brief DOM update cycle.
            this.on('dropdown_open', function () {
                setTimeout(function () {
                    if (self.$control_input && self.$control_input.length) {
                        self.$control_input[0].focus();
                    }
                }, 0);
            });
        };

        // Patch existing Selectize instances on the page.
        $(document).on('focus', '.selectize-input input', function () {
            var $input = $(this);

            // Mark as user-focused so we can restore after re-render.
            $input.data('espo-user-focused', true);
        });

        // After EspoCRM view re-renders, restore focus to the active search input.
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var mutation = mutations[i];

                if (mutation.removedNodes.length > 0) {
                    // Small delay to let the new DOM settle.
                    setTimeout(function () {
                        var $active = $('.selectize-input.focus input, .search-container input.search');

                        if ($active.length) {
                            $active[0].focus();
                        }
                    }, 50);
                    break;
                }
            }
        });

        // Observe the main content area for child changes.
        var contentEl = document.getElementById('content');

        if (contentEl) {
            observer.observe(contentEl, {childList: true, subtree: true});
        }

        // Also fix the global search bar.
        $(document).on('blur', '.global-search-container input.main-element', function () {
            var $this = $(this);

            if ($this.val() && $this.val().length > 0) {
                setTimeout(function () {
                    if (document.activeElement === document.body) {
                        $this[0].focus();
                    }
                }, 100);
            }
        });
    }

    // Run after DOM is ready and Selectize is loaded.
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(applyFix, 100);
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(applyFix, 100);
        });
    }
})();
