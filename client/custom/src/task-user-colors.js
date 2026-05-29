(function () {
    var USER_STYLES = {
        'Lamar Coates': {
            bg: 'rgba(25, 118, 210, 0.25)',
            hoverBg: 'rgba(25, 118, 210, 0.35)',
            border: '#1976d2'
        },
        'Gretchen Coates': {
            bg: 'rgba(123, 31, 162, 0.25)',
            hoverBg: 'rgba(123, 31, 162, 0.35)',
            border: '#7b1fa2'
        }
    };

    var timer = null;

    function applyRowStyles(row, style) {
        var tds = row.querySelectorAll('td');
        for (var j = 0; j < tds.length; j++) {
            tds[j].style.setProperty('background', style.bg, 'important');
            if (j === 0) {
                tds[j].style.setProperty('box-shadow', 'inset 4px 0 0 ' + style.border, 'important');
            }
        }
        row.addEventListener('mouseenter', function () {
            var cells = this.querySelectorAll('td');
            for (var k = 0; k < cells.length; k++) {
                cells[k].style.setProperty('background', style.hoverBg, 'important');
            }
        });
        row.addEventListener('mouseleave', function () {
            var cells = this.querySelectorAll('td');
            for (var k = 0; k < cells.length; k++) {
                cells[k].style.setProperty('background', style.bg, 'important');
            }
        });
    }

    function colorTaskRows() {
        var cells = document.querySelectorAll('td[data-name="assignedUser"]');

        for (var i = 0; i < cells.length; i++) {
            var row = cells[i].closest('tr');
            if (!row || row.dataset.userColored) continue;

            var userName = cells[i].textContent.trim();
            var style = USER_STYLES[userName];

            if (style) {
                applyRowStyles(row, style);
                row.dataset.userColored = '1';
            }
        }
    }

    function scheduleColor() {
        if (timer) clearTimeout(timer);
        timer = setTimeout(colorTaskRows, 150);
    }

    function init() {
        var observer = new MutationObserver(scheduleColor);
        observer.observe(document.body, {childList: true, subtree: true});
        colorTaskRows();
    }

    if (document.body) {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
