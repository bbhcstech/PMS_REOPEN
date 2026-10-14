(function () {
    'use strict';
    let chart;
    function render() {
        const card = document.getElementById('platformUserGrowthCard');
        if (!card || typeof Chart === 'undefined') return;
        const source = JSON.parse(card.dataset.userGrowth);
        const frequency = document.getElementById('userGrowthFrequency');
        const year = document.getElementById('userGrowthYear');
        const month = document.getElementById('userGrowthMonth');
        const currentYear = Number(source.today.slice(0, 4));
        const firstYear = Math.min(currentYear, ...source.records.map(row => Number(row.date.slice(0, 4))));
        if (!year.options.length) {
            for (let value = currentYear; value >= firstYear; value--) {
                const option = document.createElement('option');
                option.value = value; option.textContent = value; year.appendChild(option);
            }
        }
        const weekly = frequency.value === 'weekly';
        month.hidden = !weekly; year.hidden = weekly;
        if (!month.value || month.value > source.today.slice(0, 7)) month.value = source.today.slice(0, 7);
        const selectedYear = Number(year.value || currentYear);
        const selectedMonth = Number(month.value.slice(5, 7));
        const labels = [], total = [], active = [];
        const monthYear = Number(month.value.slice(0, 4));
        const days = new Date(Date.UTC(monthYear, selectedMonth, 0)).getUTCDate();
        const length = weekly ? Math.ceil(days / 7) : 12;
        for (let index = 0; index < length; index++) {
            const end = weekly
                ? `${month.value}-${String(Math.min((index + 1) * 7, days)).padStart(2, '0')}`
                : `${selectedYear}-${String(index + 1).padStart(2, '0')}-${new Date(Date.UTC(selectedYear, index + 1, 0)).getUTCDate()}`;
            const start = weekly ? `${month.value}-${String(index * 7 + 1).padStart(2, '0')}` : `${selectedYear}-${String(index + 1).padStart(2, '0')}-01`;
            labels.push(weekly ? `${index * 7 + 1}–${Math.min((index + 1) * 7, days)}` : new Date(Date.UTC(selectedYear, index, 1)).toLocaleString('en', { month: 'short', timeZone: 'UTC' }));
            const rows = source.records.filter(row => row.date <= end && row.date <= source.today);
            total.push(start > source.today ? null : rows.reduce((sum, row) => sum + row.total, 0));
            active.push(start > source.today ? null : rows.reduce((sum, row) => sum + row.active, 0));
        }
        if (!chart) {
            chart = new Chart(document.getElementById('userGrowthCanvas').getContext('2d'), {
                type: 'bar', data: { labels, datasets: [
                    { label: 'Total Platform Users', data: total, backgroundColor: '#2563eb', borderRadius: 6 },
                    { label: 'Currently Active Users', data: active, backgroundColor: '#16a34a', borderRadius: 6 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true } }, scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } }
                } }
            });
        } else {
            chart.data.labels = labels;
            chart.data.datasets[0].data = total;
            chart.data.datasets[1].data = active;
            chart.update();
        }
    }
    document.addEventListener('change', event => {
        if (['userGrowthFrequency', 'userGrowthYear', 'userGrowthMonth'].includes(event.target.id)) render();
    });
    document.addEventListener('pms:records-updated', render);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
    else render();
})();
