(function () {
'use strict';
let chart = null;
let frequency = 'monthly';
function render() {
    const card = document.getElementById('companyGrowthCard');
    if (!card || typeof Chart === 'undefined') return;
    const series = JSON.parse(card.dataset.growth)[frequency];
    // 1. Company Growth Line/Area Chart
    const growthCtx = document.getElementById('growthChartCanvas')?.getContext('2d');
    if (growthCtx && !chart) {
        chart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: series.labels,
                datasets: [
                    {
                        label: 'Total Companies',
                        data: series.total,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        fill: true,
                        tension: 0,
                        borderWidth: 3
                    },
                    {
                        label: 'Active Companies',
                        data: series.active,
                        borderColor: '#16a34a',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        tension: 0.35,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { family: 'Inter', size: 12 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }


    if (chart) {
        chart.data.labels = series.labels;
        chart.data.datasets[0].data = series.total;
        chart.data.datasets[1].data = series.active;
        chart.update();
    }
    card.querySelectorAll('[data-freq]').forEach(button => {
        button.classList.toggle('active', button.dataset.freq === frequency);
        button.setAttribute('aria-pressed', String(button.dataset.freq === frequency));
    });
}
document.addEventListener('click', event => {
    const button = event.target.closest('#companyGrowthCard [data-freq]');
    if (!button || !['daily', 'weekly', 'monthly'].includes(button.dataset.freq)) return;
    event.preventDefault();
    frequency = button.dataset.freq;
    render();
});
document.addEventListener('pms:records-updated', render);
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
else render();
})();
