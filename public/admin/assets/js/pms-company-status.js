(function () {
    'use strict';
    const statuses = ['active', 'trial', 'suspended', 'expired', 'pending', 'inactive'];
    const colors = ['#16a34a', '#d97706', '#dc2626', '#f43f5e', '#0284c7', '#64748b'];
    let chart;
    function render() {
        const card = document.getElementById('companyStatusCard');
        if (!card || typeof Chart === 'undefined') return;
        const counts = JSON.parse(card.dataset.statusCounts);
        const values = statuses.map(status => Number(counts[status] || 0));
        if (!chart) {
            chart = new Chart(document.getElementById('statusChartCanvas').getContext('2d'), {
                type: 'doughnut',
                data: { labels: statuses.map(status => status[0].toUpperCase() + status.slice(1)), datasets: [{
                    data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#ffffff'
                }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
            });
        } else {
            chart.data.datasets[0].data = values;
            chart.update();
        }
        card.querySelectorAll('[data-company-status]').forEach(node => {
            node.textContent = counts[node.dataset.companyStatus] || 0;
        });
        card.querySelectorAll('[data-status-extra]').forEach(node => {
            node.hidden = !counts[node.dataset.statusExtra];
        });
    }
    document.addEventListener('pms:records-updated', render);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render);
    else render();
})();
