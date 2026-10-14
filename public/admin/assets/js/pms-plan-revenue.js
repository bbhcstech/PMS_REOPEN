(function () {
'use strict';
let chart;
function render() {
 const card = document.getElementById('planRevenueCard');
 if (!card || typeof Chart === 'undefined') return;
 const totals = JSON.parse(card.dataset.planRevenue);
 const values = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'].map(tier => Number(totals[tier] || 0));
    // 2. Plan Revenue Bar Chart
    const planPerfCtx = document.getElementById('planPerformanceCanvas')?.getContext('2d');
    if (planPerfCtx && !chart) {
        chart = new Chart(planPerfCtx, {
            type: 'bar',
            data: {
                labels: ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'],
                datasets: [{
                    label: 'MRR Contribution (₹)',
                    data: values,
                    backgroundColor: ['#64748b', '#d97706', '#0284c7', '#7c3aed'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }


 if (chart) { chart.data.datasets[0].data = values; chart.update(); }
}
document.addEventListener('pms:records-updated', render);
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
else render();
})();
