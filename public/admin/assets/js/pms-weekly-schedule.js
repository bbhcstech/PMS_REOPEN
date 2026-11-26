(function () {
    'use strict';
    document.querySelectorAll('[data-weekly-schedule]').forEach(function (section) {
        var fields = Array.from(section.querySelectorAll('[data-weekly-day]'));
        var summary = section.querySelector('[data-weekly-summary]');
        function update() {
            var counts = { office: 0, wfh: 0, weekend: 0 };
            fields.forEach(function (field) { counts[field.value]++; });
            summary.textContent = '';
            [['office', 'Office', 'bg-primary'], ['wfh', 'WFH', 'bg-success'], ['weekend', 'Weekend', 'bg-secondary']].forEach(function (item) {
                var badge = document.createElement('span');
                badge.className = 'badge rounded-pill px-3 py-2 ' + item[2];
                badge.textContent = counts[item[0]] + ' ' + item[1] + ' day' + (counts[item[0]] === 1 ? '' : 's');
                summary.appendChild(badge);
            });
            fields[0].setCustomValidity(counts.office + counts.wfh > 0 ? '' : 'Choose at least one Office or WFH day.');
        }
        fields.forEach(function (field) { field.addEventListener('change', update); });
        var preset = section.querySelector('[data-weekly-preset]');
        if (preset) preset.addEventListener('click', function () {
            fields.forEach(function (field) {
                field.value = field.dataset.weeklyDay === 'Saturday' ? 'wfh' : (field.dataset.weeklyDay === 'Sunday' ? 'weekend' : 'office');
            });
            update();
        });
        update();
    });
})();
