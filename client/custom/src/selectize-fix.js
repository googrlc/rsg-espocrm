/**
 * RSG Selectize Patch — force dropdowns to render on <body>
 * Prevents clipping by panel overflow, backdrop-filter, or transforms.
 */
(function () {
    'use strict';

    function patchSelectize() {
        if (typeof $ === 'undefined' || !$.fn || !$.fn.selectize) {
            return;
        }

        var origSelectize = $.fn.selectize;

        $.fn.selectize = function (options) {
            if (options && typeof options === 'object' && !options.dropdownParent) {
                options.dropdownParent = 'body';
            }
            return origSelectize.apply(this, arguments);
        };

        for (var key in origSelectize) {
            if (Object.prototype.hasOwnProperty.call(origSelectize, key)) {
                $.fn.selectize[key] = origSelectize[key];
            }
        }
    }

    patchSelectize();
    setTimeout(patchSelectize, 0);
    setTimeout(patchSelectize, 500);
})();
