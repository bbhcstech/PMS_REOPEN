(function () {
    'use strict';
    let chart;
    function render() {
        const card = document.getElementById('dashboardPlanDistribution');
        const canvas = document.getElementById('barChart');
        if (!card || !canvas || typeof Chart === 'undefined') return;
        const counts = JSON.parse(card.dataset.counts);
        const tiers = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'];
        const values = tiers.map(tier => Number(counts[tier] || 0));
        if (chart && chart.canvas !== canvas) { chart.destroy(); chart = null; }
        if (chart) { chart.data.datasets[0].data = values; chart.update(); return; }
        chart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: { labels: tiers, datasets: [{
                label: 'Companies', data: values,
                backgroundColor: ['#2F6BFF', '#8B5CF6', '#22D3EE', '#10B981'],
                borderRadius: 8, borderSkipped: false
            }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 1400, easing: 'easeOutQuart' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(226, 232, 240, 0.6)' }, ticks: { stepSize: 1, precision: 0, font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '700' } } }
                }
            }
        });
    }
    document.addEventListener('pms:records-updated', render);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
    else render();
})();
