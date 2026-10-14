(function () {
'use strict';
let chart;
function render() {
 const card = document.getElementById('catalogDistributionCard');
 if (!card || typeof Chart === 'undefined') return;
 const counts = JSON.parse(card.dataset.distribution);
 const values = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'].map(tier => Number(counts[tier] || 0));
    // 1. Subscription Donut Chart
    const planDonutCtx = document.getElementById('planDonutCanvas')?.getContext('2d');
    if (planDonutCtx && !chart) {
        chart = new Chart(planDonutCtx, {
            type: 'doughnut',
            data: {
                labels: ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'],
                datasets: [{
                    data: values,
                    backgroundColor: ['#64748b', '#d97706', '#0284c7', '#7c3aed'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'right', labels: { font: { family: 'Inter', size: 12 } } }
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
