(function () {
    'use strict';
    const tiers = ['FREE', 'GOLD', 'PLATINUM', 'DIAMOND'];
    const colors = ['#64748b', '#d97706', '#0284c7', '#7c3aed'];
    let chart;
    function render() {
        const card = document.getElementById('subscriptionDistributionCard');
        if (!card || typeof Chart === 'undefined') return;
        const counts = JSON.parse(card.dataset.distribution);
        const values = tiers.map(tier => Number(counts[tier] || 0));
        const total = values.reduce((sum, value) => sum + value, 0);
        if (!chart) {
            chart = new Chart(document.getElementById('subscriptionDonutCanvas').getContext('2d'), {
                type: 'doughnut',
                data: { labels: tiers, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#ffffff' }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
            });
        } else {
            chart.data.datasets[0].data = values;
            chart.data.datasets[0].backgroundColor = colors;
            chart.update();
        }
        card.querySelector('.donut-center-val').textContent = total;
        card.querySelectorAll('.plan-legend-item').forEach((item, index) => {
            item.querySelector('.plan-dot').style.background = colors[index];
            item.lastElementChild.textContent = `${total ? Math.round(values[index] / total * 100) : 0}% (${values[index]})`;
        });
    }
    document.addEventListener('pms:records-updated', render);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
    else render();
})();
