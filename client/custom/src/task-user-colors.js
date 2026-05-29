(function () {
    var USER_COLORS = {
        'Lamar Coates':   '#1565c0',
        'Gretchen Coates': '#7b1fa2'
    };

    var timer = null;

    function colorUserNames() {
        var fields = document.querySelectorAll('[data-name="assignedUser"]');

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            if (field.dataset.userColored) continue;

            var link = field.querySelector('a');
            var userName = link
                ? link.textContent.trim()
                : field.textContent.trim();
            var color = USER_COLORS[userName];

            if (color && link) {
                link.style.setProperty('color', color, 'important');
                link.style.setProperty('font-weight', '600', 'important');
                field.dataset.userColored = '1';
            } else if (color) {
                field.style.setProperty('color', color, 'important');
                field.style.setProperty('font-weight', '600', 'important');
                field.dataset.userColored = '1';
            }
        }
    }

    function scheduleColor() {
        if (timer) clearTimeout(timer);
        timer = setTimeout(colorUserNames, 150);
    }

    function init() {
        var observer = new MutationObserver(scheduleColor);
        observer.observe(document.body, {childList: true, subtree: true});
        colorUserNames();
    }

    if (document.body) {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
