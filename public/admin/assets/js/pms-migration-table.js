(() => {
    const controlIds = ['migrationSearchInput', 'filterStatus', 'filterTenant', 'filterVersion'];
    const value = (id, fallback) => document.getElementById(id)?.value || fallback;
    function filterRows() {
        const query = value('migrationSearchInput', '').trim().toLowerCase();
        const status = value('filterStatus', 'all');
        const tenant = value('filterTenant', 'all');
        const version = value('filterVersion', 'all');
        document.querySelectorAll('#tenantMigrationsTable .tenant-migration-row').forEach(row => {
            const search = ['name', 'code', 'db'].some(key => (row.dataset[key] || '').toLowerCase().includes(query));
            const pending = Number(row.dataset.pending || 0);
            const matches = search && (status === 'all' || row.dataset.status === status)
                && (tenant === 'all' || row.dataset.id === tenant)
                && (version === 'all' || (version === 'latest' && pending === 0) || (version === 'outdated' && pending > 0));
            row.style.display = matches ? '' : 'none';
        });
    }
    function csvCell(value) {
        let text = String(value ?? '').trim();
        if (/^[=+\-@\t\r]/.test(text)) text = "'" + text;
        return '"' + text.replace(/"/g, '""') + '"';
    }
    function exportRows() {
        filterRows();
        const rows = Array.from(document.querySelectorAll('#tenantMigrationsTable .tenant-migration-row'))
            .filter(row => row.style.display !== 'none');
        const csv = [['Company', 'Tenant ID', 'Database', 'Current Version', 'Latest Version', 'Migration Status', 'Last Migration', 'Execution Time']];
        rows.forEach(row => {
            csv.push([row.dataset.name, row.dataset.code, row.dataset.db,
                ...Array.from(row.cells).slice(4, 9).map(cell => cell.innerText.trim())]);
        });
        const blob = new Blob(['\uFEFF' + csv.map(row => row.map(csvCell).join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'tenant-migrations.csv';
        document.body.appendChild(link);
        try { link.click(); }
        finally { link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000); }
    }
    document.addEventListener('input', event => {
        if (event.target.id === 'migrationSearchInput') filterRows();
    });
    document.addEventListener('change', event => {
        if (controlIds.includes(event.target.id)) filterRows();
    });
    document.addEventListener('click', event => {
        if (event.target.closest('#resetFiltersBtn')) {
            event.preventDefault();
            controlIds.forEach((id, index) => {
                const input = document.getElementById(id);
                if (input) input.value = index === 0 ? '' : 'all';
            });
            filterRows();
        } else if (event.target.closest('#exportMigrationsBtn')) {
            event.preventDefault();
            exportRows();
        }
    });
    document.addEventListener('pms:records-updated', filterRows);
    filterRows();
})();
