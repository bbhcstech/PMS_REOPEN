@extends('admin.layout.app')

@section('title', 'Orders Management')

@section('content')
<style>
    .payment-dropdown-btn {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        user-select: none;
    }
    .payment-dropdown-btn:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }
    .payment-dropdown-menu .dropdown-item {
        font-size: 0.82rem;
        padding: 0.45rem 1rem;
        border-radius: 6px;
    }
    .payment-dropdown-menu .dropdown-item:hover {
        background-color: #f8fafc;
    }
    .bulk-action-bar {
        transition: all 0.3s ease;
    }
    .order-item-badge-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        font-size: 0.82rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        user-select: none;
    }
    .order-item-badge-btn:hover {
        background: #eff6ff;
        border-color: #93c5fd;
        color: #1d4ed8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.12);
    }
    .order-item-badge-btn .badge-arrow {
        transition: transform 0.2s ease;
    }
    .order-item-badge-btn:hover .badge-arrow {
        transform: translateX(2px);
    }
    .modal-items-search {
        max-width: 260px;
    }
    .btn-primary,
    .btn-primary *,
    .btn-primary i,
    .btn-primary i[class^="bx"],
    .btn-primary i[class*=" bx"] {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        opacity: 1 !important;
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Top Header & Breadcrumbs -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="bx bx-cart text-primary me-2 align-middle"></i>Orders
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bx bx-package me-1"></i> Show All Product
            </a>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print
            </button>
            <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bx bx-plus-circle me-1"></i> Create Order
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">Total Orders</span>
                        <h4 class="fw-bold text-dark mb-0">{{ $stats['total_orders'] ?? 0 }}</h4>
                    </div>
                    <div class="p-3 rounded-4 bg-primary-subtle text-primary">
                        <i class="bx bx-shopping-bag fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">Completed</span>
                        <h4 class="fw-bold text-success mb-0">{{ $stats['completed'] ?? 0 }}</h4>
                    </div>
                    <div class="p-3 rounded-4 bg-success-subtle text-success">
                        <i class="bx bx-check-double fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">Processing</span>
                        <h4 class="fw-bold text-info mb-0">{{ $stats['processing'] ?? 0 }}</h4>
                    </div>
                    <div class="p-3 rounded-4 bg-info-subtle text-info">
                        <i class="bx bx-loader-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small d-block mb-1">Total Revenue</span>
                        <h4 class="fw-bold text-dark mb-0">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
                    </div>
                    <div class="p-3 rounded-4 bg-warning-subtle text-warning">
                        <i class="bx bx-dollar-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-3 align-items-center">
                <div class="col-lg-6 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Order #, client or email..." value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Completed" {{ ($status ?? '') === 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Processing" {{ ($status ?? '') === 'Processing' ? 'selected' : '' }}>Processing</option>
                        <option value="Pending" {{ ($status ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Cancelled" {{ ($status ?? '') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3 rounded-pill flex-grow-1">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                    @if(!empty($search) || ($status ?? 'all') !== 'all')
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <h5 class="fw-bold mb-0 text-dark">Order History</h5>
                <span class="text-muted small" id="selectedCountText">0 rows selected</span>
                
                <!-- Bulk Payment Actions (shown when items are selected) -->
                <div id="bulkActionBar" class="d-none align-items-center gap-2 ms-2">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary rounded-pill dropdown-toggle px-3 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-credit-card me-1"></i> Change Payment Status
                        </button>
                        <ul class="dropdown-menu shadow border-0 rounded-3 payment-dropdown-menu">
                            <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.70rem;">Set Payment For Selected</h6></li>
                            <li><a class="dropdown-item py-2 text-success" href="javascript:void(0)" onclick="bulkChangePayment('paid')"><i class="bx bx-check-circle me-2"></i> Mark as Paid</a></li>
                            <li><a class="dropdown-item py-2 text-info" href="javascript:void(0)" onclick="bulkChangePayment('partially_paid')"><i class="bx bx-time-five me-2"></i> Mark as Partially Paid</a></li>
                            <li><a class="dropdown-item py-2 text-warning" href="javascript:void(0)" onclick="bulkChangePayment('pending')"><i class="bx bx-hourglass me-2"></i> Mark as Pending</a></li>
                            <li><a class="dropdown-item py-2 text-danger" href="javascript:void(0)" onclick="bulkChangePayment('unpaid')"><i class="bx bx-x-circle me-2"></i> Mark as Unpaid</a></li>
                            <li><a class="dropdown-item py-2 text-secondary" href="javascript:void(0)" onclick="bulkChangePayment('refunded')"><i class="bx bx-reset me-2"></i> Mark as Refunded</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm rounded-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.print()"><i class="bx bx-printer text-primary me-2"></i> Print Table</a></li>
                        <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="exportTableToCsv()"><i class="bx bx-file text-success me-2"></i> Export to CSV</a></li>
                    </ul>
                </div>
                <span class="badge bg-light text-dark border">{{ count($orders) }} Records</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ordersTable">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.76rem;">
                        <th class="ps-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="checkAllOrders" onchange="toggleSelectAllOrders(this)">
                        </th>
                        <th>ORDER ID</th>
                        <th>CUSTOMER / CLIENT</th>
                        <th>DATE &amp; TIME</th>
                        <th>ITEMS</th>
                        <th>TOTAL AMOUNT</th>
                        <th>PAYMENT (Click to Edit)</th>
                        <th>STATUS</th>
                        <th class="text-end pe-4">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr id="order-row-{{ $order['id'] }}">
                            <td class="ps-4">
                                <input type="checkbox" class="form-check-input order-row-check" value="{{ $order['id'] }}" onchange="updateSelectedCount()">
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $order['project_id']) }}" class="fw-semibold text-dark font-monospace text-decoration-none hover-primary">
                                    {{ $order['order_no'] }}
                                </a>
                                <small class="text-muted d-block" style="font-size: 0.74rem;">{{ Str::limit($order['project_name'], 24) }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $order['customer_name'] }}</div>
                                <small class="text-muted">{{ $order['customer_email'] }}</small>
                            </td>
                            <td>
                                <span class="text-muted small"><i class="bx bx-time-five text-success me-1"></i>{{ $order['created_at'] }}</span>
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm order-item-badge-btn rounded-pill px-3 py-1 text-dark shadow-xs d-inline-flex align-items-center gap-1"
                                        onclick="openOrderItemsModal({{ $order['id'] }})"
                                        title="Click to view {{ $order['items_count'] }} item(s) table in this order">
                                    <i class="bx bx-package text-primary"></i>
                                    <span class="fw-semibold">{{ $order['items_count'] }} Item(S)</span>
                                    <i class="bx bx-chevron-right text-muted small badge-arrow"></i>
                                </button>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">${{ number_format($order['total_amount'], 2) }}</span>
                                @if(!empty($order['deal_reference']))
                                    <small class="text-muted d-block" style="font-size: 0.70rem;" title="From Deal: {{ $order['deal_reference'] }}">
                                        Deal: {{ Str::limit($order['deal_reference'], 14) }}
                                    </small>
                                @endif
                            </td>

                            <!-- Payment Column with Interactive Admin Status Changer -->
                            <td>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm p-0 border-0 bg-transparent payment-dropdown-btn dropdown-toggle" 
                                            type="button" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false"
                                            title="Click to change payment status">
                                        <span id="payment-badge-{{ $order['id'] }}">
                                            @if($order['payment_status'] === 'Paid')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">
                                                    <i class="bx bx-check me-1"></i>Paid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                                </span>
                                            @elseif($order['payment_status'] === 'Unpaid')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold">
                                                    <i class="bx bx-x me-1"></i>Unpaid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                                </span>
                                            @elseif($order['payment_status'] === 'Partially Paid')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-semibold">
                                                    <i class="bx bx-time-five me-1"></i>Partially Paid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                                </span>
                                            @elseif($order['payment_status'] === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold">
                                                    <i class="bx bx-hourglass me-1"></i>Pending <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-semibold">
                                                    {{ $order['payment_status'] }} <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                                </span>
                                            @endif
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu shadow border-0 rounded-3 payment-dropdown-menu">
                                        <li><h6 class="dropdown-header text-uppercase" style="font-size: 0.68rem;">Change Payment Status</h6></li>
                                        <li>
                                            <a class="dropdown-item text-success {{ $order['payment_status'] === 'Paid' ? 'fw-bold bg-light' : '' }}" 
                                               href="javascript:void(0)" 
                                               onclick="changeSinglePayment({{ $order['id'] }}, 'paid')">
                                                <i class="bx bx-check-circle me-2"></i> Paid
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-info {{ $order['payment_status'] === 'Partially Paid' ? 'fw-bold bg-light' : '' }}" 
                                               href="javascript:void(0)" 
                                               onclick="changeSinglePayment({{ $order['id'] }}, 'partially_paid')">
                                                <i class="bx bx-time-five me-2"></i> Partially Paid
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning {{ $order['payment_status'] === 'Pending' ? 'fw-bold bg-light' : '' }}" 
                                               href="javascript:void(0)" 
                                               onclick="changeSinglePayment({{ $order['id'] }}, 'pending')">
                                                <i class="bx bx-hourglass me-2"></i> Pending
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger {{ $order['payment_status'] === 'Unpaid' ? 'fw-bold bg-light' : '' }}" 
                                               href="javascript:void(0)" 
                                               onclick="changeSinglePayment({{ $order['id'] }}, 'unpaid')">
                                                <i class="bx bx-x-circle me-2"></i> Unpaid
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-secondary {{ $order['payment_status'] === 'Refunded' ? 'fw-bold bg-light' : '' }}" 
                                               href="javascript:void(0)" 
                                               onclick="changeSinglePayment({{ $order['id'] }}, 'refunded')">
                                                <i class="bx bx-reset me-2"></i> Refunded
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>

                            <td>
                                @if($order['status'] === 'Completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold">Completed</span>
                                @elseif($order['status'] === 'Processing')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-semibold">Processing</span>
                                @elseif($order['status'] === 'Pending')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold">Pending</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-semibold">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.show', $order['project_id']) }}">
                                                <i class="bx bx-show text-primary me-2"></i> View Project Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('tasks.index', ['project_id' => $order['project_id']]) }}">
                                                <i class="bx bx-check-square text-info me-2"></i> Manage Tasks
                                            </a>
                                        </li>
                                        @if($order['project']->client_id)
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ route('clients.show', $order['project']->client_id) }}">
                                                    <i class="bx bx-user text-success me-2"></i> View Client Profile
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ route('projects.edit', $order['project_id']) }}">
                                                <i class="bx bx-edit text-warning me-2"></i> Edit Project
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bx bx-cart fs-1 d-block mb-2 text-secondary"></i>
                                No client project orders found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Toast Container for instant feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
    <div id="paymentToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bx bx-check-circle text-success fs-4" id="toastIcon"></i>
                <span id="toastMessage">Payment status updated successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Order Items Breakdown Modal -->
<div class="modal fade" id="orderItemsModal" tabindex="-1" aria-labelledby="orderItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bx bx-package fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-dark mb-0" id="orderItemsModalLabel">Order Items Breakdown</h5>
                            <span class="badge bg-light text-dark border font-monospace px-2 py-1" id="modalOrderNo">ORD-0000</span>
                        </div>
                        <small class="text-muted d-block" id="modalProjectName">Project Items &amp; Deliverables Table</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span id="modalPaymentStatusBadge"></span>
                    <span class="badge bg-light text-dark border px-3 py-2 fw-bold" id="modalTotalAmount">$0.00</span>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Info Banner -->
            <div class="bg-light px-4 py-3 border-bottom">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-muted small"><i class="bx bx-user me-1 text-primary"></i>Client / Customer:</div>
                            <span class="fw-semibold text-dark small" id="modalCustomerName">-</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-muted small"><i class="bx bx-calendar me-1 text-info"></i>Order Date:</div>
                            <span class="fw-semibold text-dark small" id="modalCreatedAt">-</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-inline-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-1" id="modalItemCountBadge">0 Items</span>
                            <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1 small" id="modalCompletedBadge">0 Completed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body with Search & Items Table -->
            <div class="modal-body p-4">
                <!-- Search & Filters Toolbar -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 320px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="modalItemSearchInput" placeholder="Search items, tasks, assignee..." oninput="filterModalItems()">
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Filter:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active modal-filter-btn" data-filter="all" onclick="setModalFilter('all', this)">All</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn" data-filter="completed" onclick="setModalFilter('completed', this)">Completed</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn" data-filter="in progress" onclick="setModalFilter('in progress', this)">In Progress</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn" data-filter="pending" onclick="setModalFilter('pending', this)">Pending</button>
                        </div>
                    </div>
                </div>

                <!-- Items Table Container -->
                <div class="table-responsive border rounded-3 overflow-hidden">
                    <table class="table table-hover align-middle mb-0" id="modalItemsTable">
                        <thead class="bg-light">
                            <tr class="text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem;">
                                <th class="ps-3" style="width: 45px;">#</th>
                                <th style="width: 130px;">ITEM / TASK CODE</th>
                                <th>ITEM NAME &amp; DESCRIPTION</th>
                                <th>ASSIGNED TO</th>
                                <th>PRIORITY</th>
                                <th>PROGRESS &amp; STATUS</th>
                                <th>TIMELINE / DUE DATE</th>
                                <th>EST. HOURS</th>
                                <th class="text-end pe-3">ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty Search / Items State -->
                <div id="modalEmptyState" class="d-none text-center py-5">
                    <div class="p-3 bg-light rounded-circle d-inline-flex mb-2">
                        <i class="bx bx-package fs-2 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No Matching Items Found</h6>
                    <p class="text-muted small mb-0">Try changing your search query or filter option.</p>
                </div>
            </div>

            <!-- Modal Footer with Quick Links -->
            <div class="modal-footer bg-light py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="modalTasksUrl" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm">
                        <i class="bx bx-list-check me-1"></i> Manage Tasks Board
                    </a>
                    <a href="#" id="modalProjectUrl" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                        <i class="bx bx-show me-1"></i> View Project Details
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="modalCreateTaskUrl" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bx bx-plus-circle me-1"></i> Add New Task / Item
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const ORDERS_DATA = @json($orders);
let currentModalOrder = null;
let currentModalFilter = 'all';

function renderPaymentBadge(status) {
    const s = String(status).toLowerCase();
    if (s === 'paid') {
        return `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold"><i class="bx bx-check me-1"></i>Paid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i></span>`;
    } else if (s === 'unpaid') {
        return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-semibold"><i class="bx bx-x me-1"></i>Unpaid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i></span>`;
    } else if (s === 'partially_paid' || s === 'partially paid' || s === 'partial') {
        return `<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-semibold"><i class="bx bx-time-five me-1"></i>Partially Paid <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i></span>`;
    } else if (s === 'pending') {
        return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold"><i class="bx bx-hourglass me-1"></i>Pending <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i></span>`;
    } else {
        return `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-semibold">${status} <i class="bx bx-chevron-down ms-1" style="font-size: 0.7rem;"></i></span>`;
    }
}

function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('paymentToast');
    const msgEl = document.getElementById('toastMessage');
    const iconEl = document.getElementById('toastIcon');
    
    if (msgEl) msgEl.textContent = message;
    if (iconEl) {
        iconEl.className = isSuccess ? 'bx bx-check-circle text-success fs-4' : 'bx bx-error-circle text-danger fs-4';
    }
    
    if (toastEl && window.bootstrap) {
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }
}

function changeSinglePayment(projectId, newStatus) {
    fetch(`{{ url('/orders') }}/${projectId}/payment-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ payment_status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badgeEl = document.getElementById(`payment-badge-${projectId}`);
            if (badgeEl) {
                badgeEl.innerHTML = renderPaymentBadge(data.payment_status);
            }
            showToast(data.message, true);
        } else {
            showToast(data.message || 'Failed to update payment status.', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Network error while updating payment status.', false);
    });
}

function bulkChangePayment(newStatus) {
    const selectedIds = Array.from(document.querySelectorAll('.order-row-check:checked')).map(c => c.value);
    if (!selectedIds.length) {
        showToast('Please select at least one order to update.', false);
        return;
    }

    fetch('{{ route("orders.bulk-payment-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids: selectedIds, payment_status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            selectedIds.forEach(id => {
                const badgeEl = document.getElementById(`payment-badge-${id}`);
                if (badgeEl) {
                    badgeEl.innerHTML = renderPaymentBadge(data.payment_status);
                }
            });
            showToast(data.message, true);
        } else {
            showToast(data.message || 'Bulk update failed.', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Network error during bulk update.', false);
    });
}

function toggleSelectAllOrders(masterCheck) {
    const checks = document.querySelectorAll('.order-row-check');
    checks.forEach(c => c.checked = masterCheck.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedCount = document.querySelectorAll('.order-row-check:checked').length;
    const textEl = document.getElementById('selectedCountText');
    const bulkBar = document.getElementById('bulkActionBar');
    
    if (textEl) {
        textEl.textContent = `${checkedCount} rows selected`;
    }
    
    if (bulkBar) {
        if (checkedCount > 0) {
            bulkBar.classList.remove('d-none');
            bulkBar.classList.add('d-flex');
        } else {
            bulkBar.classList.add('d-none');
            bulkBar.classList.remove('d-flex');
        }
    }
}

function exportTableToCsv() {
    const rows = [["Order ID", "Customer", "Date", "Items", "Amount", "Payment", "Status"]];
    document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
        const cols = tr.querySelectorAll('td');
        if (cols.length >= 8) {
            rows.push([
                cols[1].innerText.trim().replace(/\n/g, ' '),
                cols[2].innerText.trim().replace(/\n/g, ' '),
                cols[3].innerText.trim(),
                cols[4].innerText.trim(),
                cols[5].innerText.trim(),
                cols[6].innerText.trim(),
                cols[7].innerText.trim()
            ]);
        }
    });
    
    let csvContent = "data:text/csv;charset=utf-8," + rows.map(e => e.map(i => `"${i}"`).join(",")).join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "Orders_Client_Projects_" + new Date().toISOString().slice(0,10) + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ----------------------------------------------------
// Order Items Breakdown Modal Functions
// ----------------------------------------------------
function openOrderItemsModal(orderId) {
    const order = ORDERS_DATA.find(o => String(o.id) === String(orderId));
    if (!order) {
        // Fallback to fetch via AJAX if not found in preloaded array
        fetch(`{{ url('/orders') }}/${orderId}/items`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderOrderModal(data.order, data.items);
            } else {
                showToast('Failed to load order items.', false);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error loading order items.', false);
        });
        return;
    }

    renderOrderModal(order, order.items || []);
}

function renderOrderModal(order, items) {
    currentModalOrder = { order, items };
    currentModalFilter = 'all';
    
    // Reset search input & filter buttons
    const searchInput = document.getElementById('modalItemSearchInput');
    if (searchInput) searchInput.value = '';
    
    document.querySelectorAll('.modal-filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-filter') === 'all');
    });

    // Populate Headers
    document.getElementById('modalOrderNo').textContent = order.order_no || `ORD-${order.id}`;
    document.getElementById('modalProjectName').textContent = order.project_name || 'Project Deliverables';
    document.getElementById('modalCustomerName').textContent = order.customer_name + (order.customer_company ? ` (${order.customer_company})` : '');
    document.getElementById('modalCreatedAt').textContent = order.created_at || '-';
    document.getElementById('modalTotalAmount').textContent = `$${parseFloat(order.total_amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    
    // Payment badge
    const paymentBadgeEl = document.getElementById('modalPaymentStatusBadge');
    if (paymentBadgeEl) {
        paymentBadgeEl.innerHTML = renderPaymentBadge(order.payment_status || 'Paid');
    }

    // Action links
    const tasksUrlEl = document.getElementById('modalTasksUrl');
    if (tasksUrlEl) tasksUrlEl.href = order.tasks_url || `{{ url('/tasks') }}?project_id=${order.id}`;

    const projectUrlEl = document.getElementById('modalProjectUrl');
    if (projectUrlEl) projectUrlEl.href = order.project_url || `{{ url('/projects') }}/${order.id}`;

    const createTaskUrlEl = document.getElementById('modalCreateTaskUrl');
    if (createTaskUrlEl) createTaskUrlEl.href = order.create_task_url || `{{ url('/tasks/create') }}?project_id=${order.id}`;

    // Render Table Rows
    renderModalItemsTable(items);

    // Show Modal
    const modalEl = document.getElementById('orderItemsModal');
    if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }
}

function renderModalItemsTable(items) {
    const tbody = document.getElementById('modalItemsTableBody');
    const emptyState = document.getElementById('modalEmptyState');
    const tableContainer = document.querySelector('#orderItemsModal .table-responsive');
    
    if (!tbody) return;
    tbody.innerHTML = '';

    // Calculate completed stats
    const totalCount = items.length;
    const completedCount = items.filter(i => String(i.status).toLowerCase() === 'completed' || i.is_completed).length;
    
    const countBadge = document.getElementById('modalItemCountBadge');
    if (countBadge) countBadge.textContent = `${totalCount} Item${totalCount !== 1 ? 's' : ''}`;
    
    const completedBadge = document.getElementById('modalCompletedBadge');
    if (completedBadge) completedBadge.textContent = `${completedCount} Completed`;

    // Filter items based on current search and status
    const searchTerm = (document.getElementById('modalItemSearchInput')?.value || '').toLowerCase().trim();
    
    const filteredItems = items.filter(item => {
        const itemStatus = String(item.status || '').toLowerCase();
        let matchesStatus = true;
        if (currentModalFilter === 'completed') {
            matchesStatus = (itemStatus === 'completed' || item.is_completed);
        } else if (currentModalFilter === 'in progress') {
            matchesStatus = (itemStatus === 'in progress' || itemStatus === 'processing' || itemStatus === 'doing');
        } else if (currentModalFilter === 'pending') {
            matchesStatus = (itemStatus === 'pending' || itemStatus === 'not started' || itemStatus === 'to do');
        }

        let matchesSearch = true;
        if (searchTerm) {
            const code = String(item.code || '').toLowerCase();
            const title = String(item.title || '').toLowerCase();
            const desc = String(item.description || '').toLowerCase();
            const assignee = String(item.assignee_name || '').toLowerCase();
            matchesSearch = code.includes(searchTerm) || title.includes(searchTerm) || desc.includes(searchTerm) || assignee.includes(searchTerm);
        }

        return matchesStatus && matchesSearch;
    });

    if (filteredItems.length === 0) {
        if (emptyState) emptyState.classList.remove('d-none');
        if (tableContainer) tableContainer.classList.add('d-none');
        return;
    }

    if (emptyState) emptyState.classList.add('d-none');
    if (tableContainer) tableContainer.classList.remove('d-none');

    filteredItems.forEach((item, index) => {
        const tr = document.createElement('tr');
        
        // Priority Badge
        let priorityBadge = `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">${item.priority || 'Normal'}</span>`;
        const pLower = String(item.priority || '').toLowerCase();
        if (pLower === 'high' || pLower === 'urgent') {
            priorityBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small fw-semibold"><i class="bx bx-up-arrow-alt me-1"></i>${item.priority}</span>`;
        } else if (pLower === 'medium' || pLower === 'normal') {
            priorityBadge = `<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 small fw-semibold">${item.priority}</span>`;
        } else if (pLower === 'low') {
            priorityBadge = `<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">${item.priority}</span>`;
        }

        // Status Badge & Progress
        let statusBadge = `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">${item.status}</span>`;
        const sLower = String(item.status || '').toLowerCase();
        if (sLower === 'completed' || item.is_completed) {
            statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small fw-semibold"><i class="bx bx-check me-1"></i>Completed</span>`;
        } else if (sLower === 'in progress' || sLower === 'processing' || sLower === 'doing') {
            statusBadge = `<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small fw-semibold"><i class="bx bx-loader-circle me-1"></i>In Progress</span>`;
        } else if (sLower === 'pending' || sLower === 'not started' || sLower === 'to do') {
            statusBadge = `<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 small fw-semibold"><i class="bx bx-time me-1"></i>Pending</span>`;
        } else if (sLower === 'on hold' || sLower === 'waiting') {
            statusBadge = `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">${item.status}</span>`;
        }

        const progressPercent = Math.min(100, Math.max(0, parseInt(item.progress || (item.is_completed ? 100 : 0))));
        const progressBarColor = progressPercent === 100 ? 'bg-success' : (progressPercent > 50 ? 'bg-primary' : 'bg-warning');

        // Assignee Avatar
        let avatarHtml = `<div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-xs rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.72rem;">
                ${(item.assignee_name || 'U').charAt(0).toUpperCase()}
            </div>
            <span class="small fw-semibold text-dark">${item.assignee_name || 'Unassigned'}</span>
        </div>`;
        if (item.assignee_avatar) {
            avatarHtml = `<div class="d-flex align-items-center gap-2">
                <img src="${item.assignee_avatar}" alt="${item.assignee_name}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                <span class="small fw-semibold text-dark">${item.assignee_name}</span>
            </div>`;
        }

        tr.innerHTML = `
            <td class="ps-3 text-muted small">${index + 1}</td>
            <td>
                <span class="badge bg-light text-dark border font-monospace small px-2 py-1">${item.code || '-'}</span>
            </td>
            <td>
                <div class="fw-bold text-dark">${item.title}</div>
                <small class="text-muted d-block text-truncate" style="max-width: 280px;" title="${item.description || ''}">
                    ${item.description || '-'}
                </small>
            </td>
            <td>${avatarHtml}</td>
            <td>${priorityBadge}</td>
            <td>
                <div class="d-flex flex-column gap-1" style="min-width: 110px;">
                    <div>${statusBadge}</div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar ${progressBarColor}" role="progressbar" style="width: ${progressPercent}%" aria-valuenow="${progressPercent}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </td>
            <td>
                <span class="small text-dark"><i class="bx bx-calendar text-muted me-1"></i>${item.due_date || 'N/A'}</span>
            </td>
            <td>
                <span class="small fw-semibold text-muted font-monospace">${item.estimate_hours || '-'}</span>
            </td>
            <td class="text-end pe-3">
                <a href="${item.view_url || '#'}" class="btn btn-sm btn-icon btn-light rounded-circle shadow-2xs hover-primary" title="View Details">
                    <i class="bx bx-right-arrow-alt fs-5"></i>
                </a>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function filterModalItems() {
    if (currentModalOrder) {
        renderModalItemsTable(currentModalOrder.items || []);
    }
}

function setModalFilter(filter, btnEl) {
    currentModalFilter = filter;
    document.querySelectorAll('.modal-filter-btn').forEach(btn => btn.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    filterModalItems();
}
</script>
@endsection
