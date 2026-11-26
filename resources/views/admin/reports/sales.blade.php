@extends('admin.layout.app')

@section('title', 'Sales Report')

@section('content')
<div class="container-fluid px-4 pt-1 pb-4 sales-report-page">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4 sales-report-header">
        <div class="sales-report-header__left">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="header-icon-badge sales-report-header__icon" aria-hidden="true">
                    <i class="bx bx-cart"></i>
                </div>
                <h3 id="sales-report-heading" class="sales-report-header__title mb-0">Sales Report</h3>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 sales-report-header__meta">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb sales-report-breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
                    </ol>
                </nav>
                <span class="sales-report-header__dot" aria-hidden="true">·</span>
                <p class="sales-report-header__subtitle mb-0">Monitor, track, and analyze sales transactions, invoices, and revenue across the organisation.</p>
            </div>
        </div>

        <div class="sales-sync-pill" aria-label="Sales status: Revenue Data Synchronized">
            <span class="sales-sync-pill__dot" aria-hidden="true"></span>
            <span class="sales-sync-pill__text">Revenue Data Synchronized</span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-md-6">
            <div class="card sales-report-stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="sales-report-stat-card__label">Total Sales Transactions</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="sales-report-stat-card__value">{{ $sales->count() }}</span>
                            <span class="sales-report-stat-card__unit">transactions</span>
                        </div>
                    </div>
                    <div class="stat-icon sales-report-stat-icon sales-report-stat-icon--blue" aria-hidden="true">
                        <i class="bx bx-cart"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6">
            <div class="card sales-report-stat-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="sales-report-stat-card__label">Total Sales Revenue</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="sales-report-stat-card__value">${{ number_format($totalSales, 2) }}</span>
                            <span class="sales-report-stat-card__unit">total revenue</span>
                        </div>
                    </div>
                    <div class="stat-icon sales-report-stat-icon sales-report-stat-icon--green" aria-hidden="true">
                        <i class="bx bx-dollar-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card sales-report-card sales-report-table-card">
        <div class="card-body">
            <div id="salesToolbarHost" class="sales-toolbar-host" data-live-preserve></div>
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="sales-report-heading">
                <table id="salesTable" data-pms-toolbar-host="salesToolbarHost" class="table table-hover align-middle w-100 sales-report-table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Invoice / Reference</th>
                            <th scope="col">Project</th>
                            <th scope="col">Sales Revenue</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $key => $sale)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td><div class="fw-semibold text-dark">Invoice #{{ $sale->invoice_id ?? $sale->id }}</div></td>
                                <td>{{ $sale->project->name ?? 'General Sales' }}</td>
                                <td><span class="badge bg-success-subtle text-success fw-bold fs-6 px-3 py-1">${{ number_format($sale->amount, 2) }}</span></td>
                                <td>{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('d M Y') : '—' }}</td>
                                <td><span class="badge bg-success rounded-pill px-3 py-1">Completed</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sales-report-page {
        accent-color: var(--pms-primary, #2F6BFF);
    }

    .sales-report-header__left {
        min-width: 0;
        flex: 1 1 320px;
    }

    .sales-report-header__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .sales-report-header__title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--pms-text, #0F172A);
    }

    .sales-report-breadcrumb {
        font-size: 0.78rem;
        font-weight: 500;
    }

    .sales-report-breadcrumb .breadcrumb-item a {
        color: var(--pms-muted, #64748B);
    }

    .sales-report-breadcrumb .breadcrumb-item.active {
        color: var(--pms-text, #0F172A);
        font-weight: 600;
    }

    .sales-report-header__dot {
        color: var(--pms-muted, #94A3B8);
        font-weight: 700;
    }

    .sales-report-header__subtitle {
        font-size: 0.82rem;
        color: var(--pms-muted, #64748B);
    }

    .sales-sync-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.9rem;
        border-radius: 10px;
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        flex-shrink: 0;
    }

    .sales-sync-pill__dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10B981;
        flex-shrink: 0;
    }

    .sales-sync-pill__text {
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--pms-text, #334155);
        white-space: nowrap;
    }

    .sales-report-stat-card,
    .sales-report-card {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        border-radius: 16px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .sales-report-stat-card .card-body {
        padding: 1.25rem 1.35rem;
    }

    .sales-report-stat-card__label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--pms-muted, #64748B);
        margin-bottom: 0.55rem;
    }

    .sales-report-stat-card__value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1.1;
        color: var(--pms-text, #0F172A);
    }

    .sales-report-stat-card__unit {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
    }

    .sales-report-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .sales-report-stat-icon--blue {
        background: #EFF6FF;
        border: 1px solid #DBEAFE;
        color: #2F6BFF;
    }

    .sales-report-stat-icon--green {
        background: #ECFDF5;
        border: 1px solid #D1FAE5;
        color: #10B981;
    }

    .sales-report-table-card .card-body {
        padding: 0;
    }

    .sales-toolbar-host {
        width: 100%;
    }

    .sales-report-table-card .pms-table-tools {
        background: var(--pms-surface, #FFFFFF);
        border: 0;
        border-bottom: 1px solid var(--pms-border, #E2E8F0);
        border-radius: 16px 16px 0 0;
        min-height: 62px;
        padding: 0.85rem 1.35rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: flex-start;
    }

    .sales-report-table-card .pms-table-tools__status {
        background: var(--pms-surface-soft, #F1F5F9);
        color: var(--pms-text, #334155);
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        margin-right: 0;
        white-space: nowrap;
    }

    .sales-toolbar__subtitle {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--pms-muted, #64748B);
        margin-right: auto;
    }

    .sales-report-table-card .dataTables_filter {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100%;
    }

    .sales-report-table-card .dataTables_filter label {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin: 0;
    }

    .sales-search-icon {
        position: absolute;
        left: 0.75rem;
        font-size: 0.95rem;
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
        pointer-events: none;
    }

    .sales-report-table-card .dataTables_filter input {
        border-radius: 10px !important;
        padding: 0.42rem 0.85rem 0.42rem 2.15rem !important;
        border: 1px solid var(--pms-border, #E2E8F0) !important;
        background: var(--pms-surface-soft, #F8FAFC) !important;
        font-size: 0.82rem;
        height: 36px;
        min-width: 210px;
        margin-left: 0 !important;
    }

    .sales-report-table-card .pms-table-export {
        margin-left: 0;
    }

    .sales-report-table-card .pms-table-export__toggle {
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155);
        font-weight: 600;
        font-size: 0.82rem;
        min-height: 36px;
        padding: 0.38rem 0.85rem;
        border-radius: 10px;
    }

    .sales-report-table-card .pms-table-export__toggle:hover,
    .sales-report-table-card .pms-table-export__toggle:focus-visible {
        background: var(--pms-surface-soft, #F1F5F9);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A);
        box-shadow: none;
        transform: none;
    }

    .sales-report-table-card .pms-table-scroll-host {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow-x: auto !important;
    }

    .sales-report-table {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .sales-report-table thead th {
        background: #F8FAFC !important;
        color: #64748B !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #E2E8F0 !important;
        padding: 0.85rem 1rem !important;
        white-space: nowrap;
    }

    .sales-report-table td.dataTables_empty {
        padding: 0 !important;
        border-bottom: 0 !important;
        background: transparent !important;
    }

    .sales-report-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4.5rem 1.5rem;
    }

    .sales-report-empty__icon {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #F1F5F9;
        color: #64748B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .content-wrapper .sales-report-empty__icon i.bx-cart,
    .sales-report-empty__icon i.bx-cart {
        color: #64748B !important;
        -webkit-text-fill-color: #64748B !important;
    }

    .sales-report-empty__badge {
        position: absolute;
        right: -4px;
        bottom: -4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #EFF6FF;
        border: 2px solid #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        color: #2F6BFF !important;
    }

    .sales-report-empty__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--pms-text, #0F172A);
        margin-bottom: 0.4rem;
    }

    .sales-report-empty__text {
        max-width: 340px;
        font-size: 0.8rem;
        line-height: 1.55;
        color: var(--pms-muted, #64748B);
    }

    .sales-report-empty__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        padding: 0.45rem 0.95rem;
        border-radius: 8px;
        background: var(--pms-surface, #FFFFFF);
        border: 1px solid var(--pms-border, #E2E8F0);
        color: var(--pms-text, #334155) !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .sales-report-empty__btn:hover,
    .sales-report-empty__btn:focus-visible {
        background: var(--pms-surface-soft, #F8FAFC);
        border-color: #CBD5E1;
        color: var(--pms-text, #0F172A) !important;
    }

    .sales-dt-footer {
        padding: 0.85rem 1.35rem;
        border-top: 1px solid var(--pms-border, #E2E8F0);
    }

    .sales-report-table-card .dataTables_info {
        font-size: 0.78rem !important;
        color: var(--pms-muted, #64748B) !important;
        padding: 0 !important;
    }

    .sales-report-table-card .dataTables_paginate {
        padding: 0 !important;
    }

    .sales-report-table-card .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 0.78rem !important;
    }

    html[data-pms-theme="dark"] .sales-report-header__icon,
    html[data-pms-theme="dark"] .sales-report-stat-icon--blue {
        background: rgba(47, 107, 255, 0.16);
        border-color: rgba(47, 107, 255, 0.3);
        color: #60A5FA;
    }

    html[data-pms-theme="dark"] .sales-report-stat-icon--green {
        background: rgba(16, 185, 129, 0.16);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    html[data-pms-theme="dark"] .sales-report-table-card .pms-table-tools {
        background: #0F1530 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .sales-report-table-card .pms-table-tools__status {
        background: #141B3D !important;
    }

    html[data-pms-theme="dark"] .sales-report-table thead th {
        background: #141B3D !important;
        color: #CBD5E1 !important;
        border-bottom-color: rgba(238, 241, 251, 0.1) !important;
    }

    html[data-pms-theme="dark"] .sales-report-empty__icon {
        background: rgba(148, 163, 184, 0.14);
    }

    html[data-pms-theme="dark"] .content-wrapper .sales-report-empty__icon i.bx-cart,
    html[data-pms-theme="dark"] .sales-report-empty__icon i.bx-cart {
        color: #94A3B8 !important;
        -webkit-text-fill-color: #94A3B8 !important;
    }

    html[data-pms-theme="dark"] .sales-report-empty__badge {
        background: #1E293B;
        border-color: #0F1530;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    $(document).ready(function () {
        var emptyStateHtml =
            '<div class="sales-report-empty">' +
                '<div class="stat-icon sales-report-empty__icon" aria-hidden="true">' +
                    '<i class="bx bx-cart"></i>' +
                    '<span class="sales-report-empty__badge"><i class="bx bx-search"></i></span>' +
                '</div>' +
                '<div class="sales-report-empty__title">No sales found</div>' +
                '<p class="sales-report-empty__text mb-3">Try clearing your search query or refreshing the report to view sales transactions.</p>' +
                '<a href="{{ route('reports.sales') }}" class="sales-report-empty__btn"><i class="bx bx-refresh" aria-hidden="true"></i> Reset Search</a>' +
            '</div>';

        var table = $('#salesTable').DataTable({
            dom: '<"d-none"B><"sales-dt-search"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 sales-dt-footer"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy' },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV' },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel' },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print' }
            ],
            language: {
                search: '',
                searchPlaceholder: 'Search within sales...',
                emptyTable: emptyStateHtml,
                zeroRecords: emptyStateHtml
            },
            pageLength: 25
        });

        function mountSalesToolbar() {
            var toolbar = document.querySelector('#salesToolbarHost .pms-table-tools');
            var filterBox = document.getElementById('salesTable_filter');
            if (!toolbar) return;

            var status = toolbar.querySelector('.pms-table-tools__status');
            if (status && !toolbar.querySelector('.sales-toolbar__subtitle')) {
                var subtitle = document.createElement('span');
                subtitle.className = 'sales-toolbar__subtitle';
                subtitle.textContent = 'Showing all sales transactions';
                status.insertAdjacentElement('afterend', subtitle);
            }

            if (filterBox) {
                var label = filterBox.querySelector('label');
                if (label && !label.querySelector('.sales-search-icon')) {
                    var icon = document.createElement('i');
                    icon.className = 'bx bx-search sales-search-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    label.prepend(icon);
                }
                var exportBox = toolbar.querySelector('.pms-table-export');
                if (exportBox && filterBox.parentNode !== toolbar) {
                    toolbar.insertBefore(filterBox, exportBox);
                }
            }
        }

        window.requestAnimationFrame(mountSalesToolbar);
        table.on('draw.dt', mountSalesToolbar);

        var toolbarHost = document.getElementById('salesToolbarHost');
        if (toolbarHost && window.MutationObserver) {
            new MutationObserver(mountSalesToolbar).observe(toolbarHost, { childList: true });
        }
    });
</script>
@endpush
