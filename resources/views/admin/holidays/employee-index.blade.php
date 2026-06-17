@extends('admin.layout.app')
@section('title', 'Holiday Calendar')

@section('content')
<main class="main">
    <div class="employee-holiday-page container-xxl py-4">
        @php
            $monthName = $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->format('F') : 'All Months';
            $exportParams = array_filter(['year' => $selectedYear, 'month' => $selectedMonth]);
            $snapshotRows = $holidays->map(function ($holiday) {
                return [
                    'date' => \Carbon\Carbon::parse($holiday->date)->format('d M Y'),
                    'day' => \Carbon\Carbon::parse($holiday->date)->format('l'),
                    'title' => $holiday->occassion ?: $holiday->title,
                    'type' => $holiday->type === 'weekly_holiday' ? 'Weekly' : 'Special',
                ];
            })->values();
        @endphp

        <section class="employee-holiday-hero mb-4">
            <div>
                <span><i class="bi bi-calendar-heart"></i> Employee Holiday View</span>
                <h1>Holiday Calendar {{ $selectedYear }}</h1>
                <p>View organization holidays by year, month, day, or full calendar so your planning stays clear.</p>
            </div>
            <div class="employee-holiday-actions">
                <a href="{{ route('employee.holidays.calendar', ['year' => $selectedYear]) }}" class="btn btn-outline-light fw-bold"><i class="bi bi-calendar3 me-1"></i>Calendar</a>
                <a href="{{ route('holidays.export', $exportParams) }}" class="btn btn-outline-light fw-bold"><i class="bi bi-file-earmark-excel me-1"></i>Export</a>
                <button type="button" id="employeeHolidayScreenshot" class="btn btn-outline-light fw-bold"><i class="bi bi-camera me-1"></i>Screenshot</button>
            </div>
        </section>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="employee-holiday-stat"><small>Total</small><strong>{{ $stats['total'] }}</strong></div></div>
            <div class="col-md-3"><div class="employee-holiday-stat"><small>Special</small><strong>{{ $stats['special'] }}</strong></div></div>
            <div class="col-md-3"><div class="employee-holiday-stat"><small>Weekly</small><strong>{{ $stats['weekly'] }}</strong></div></div>
            <div class="col-md-3"><div class="employee-holiday-stat"><small>Months</small><strong>{{ $stats['months'] }}</strong></div></div>
        </div>

        <div class="employee-holiday-toolbar mb-4">
            <div class="employee-filter-title">
                <i class="bi bi-funnel"></i>
                <div>
                    <h3>Filter Holiday List</h3>
                    <p>Filter by year, month, day, or holiday type.</p>
                </div>
            </div>
            <form method="GET" action="{{ route('employee.holidays') }}" class="employee-holiday-filter">
                <div>
                    <label>Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ (int) $selectedMonth === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Year</label>
                    <select name="year" class="form-select">
                        @foreach(range(date('Y') - 2, date('Y') + 3) as $y)
                            <option value="{{ $y }}" {{ (int) $selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Day</label>
                    <select id="employeeHolidayDayFilter" class="form-select">
                        <option value="">All Days</option>
                        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Type</label>
                    <select id="employeeHolidayTypeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="Special Holiday">Special Holiday</option>
                        <option value="Weekly Holiday">Weekly Holiday</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('employee.holidays') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>

        <div class="employee-holiday-panel">
            <div class="employee-holiday-panel-head">
                <div>
                    <h3>Holiday List</h3>
                    <p>Every row includes date, day, and why the holiday is set.</p>
                </div>
                <div class="employee-selection-tools">
                    <span id="employeeHolidaySelectedCount">0 selected</span>
                    <button type="button" id="employeeHolidayClearSelection" class="btn btn-sm btn-outline-secondary" disabled>Clear</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="employeeHolidayTable" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="52">
                                <input type="checkbox" id="employeeHolidaySelectAll" class="form-check-input" aria-label="Select all holidays">
                            </th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Why Holiday Is Set</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                            @php $date = \Carbon\Carbon::parse($holiday->date); @endphp
                            <tr>
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        class="form-check-input employee-holiday-checkbox"
                                        aria-label="Select {{ $holiday->occassion ?: $holiday->title }}"
                                        data-date="{{ $date->format('d M Y') }}"
                                        data-day="{{ $date->format('l') }}"
                                        data-title="{{ $holiday->occassion ?: $holiday->title }}"
                                        data-type="{{ $holiday->type === 'weekly_holiday' ? 'Weekly' : 'Special' }}"
                                    >
                                </td>
                                <td data-order="{{ $date->format('Y-m-d') }}"><strong>{{ $date->format('d M Y') }}</strong></td>
                                <td><span class="employee-day">{{ $date->format('l') }}</span></td>
                                <td>
                                    <div class="employee-reason">{{ $holiday->occassion ?: $holiday->title }}</div>
                                    <small class="text-muted">{{ $date->format('F Y') }}</small>
                                </td>
                                <td><span class="employee-badge {{ $holiday->type === 'weekly_holiday' ? 'weekly' : 'special' }}">{{ $holiday->type === 'weekly_holiday' ? 'Weekly Holiday' : 'Special Holiday' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No holidays found for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('css')
<style>
    .employee-holiday-hero { display: flex; justify-content: space-between; align-items: center; gap: 22px; min-height: 220px; padding: 30px; border-radius: 18px; color: #fff; background: linear-gradient(135deg, #0f766e, #2563eb 58%, #7c3aed); box-shadow: 0 22px 55px rgba(31,41,55,.18); animation: holidayRise .5s ease both; }
    .employee-holiday-hero span { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.18); font-weight: 800; }
    .employee-holiday-hero h1 { color: #fff; margin: 12px 0 8px; font-size: clamp(2rem, 4vw, 3.4rem); font-weight: 900; letter-spacing: 0; }
    .employee-holiday-hero p { margin: 0; color: rgba(255,255,255,.9); font-weight: 700; }
    .employee-holiday-actions, .employee-holiday-filter { display: flex; align-items: end; gap: 10px; flex-wrap: wrap; }
    .employee-holiday-toolbar { display: grid; gap: 16px; padding: 20px; border: 1px solid rgba(23,32,51,.1); border-radius: 16px; background: rgba(255,255,255,.96); box-shadow: 0 14px 35px rgba(28,37,65,.1); animation: holidayRise .55s ease both; }
    .employee-filter-title { display: flex; align-items: center; gap: 12px; }
    .employee-filter-title > i { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; background: #dbeafe; color: #2563eb; font-size: 1.2rem; }
    .employee-filter-title h3 { margin: 0; font-weight: 900; font-size: 1.08rem; }
    .employee-filter-title p { margin: 3px 0 0; color: #667085; font-weight: 650; }
    .employee-holiday-filter label { display: block; margin-bottom: 6px; color: #667085; font-size: .78rem; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
    .employee-holiday-filter .form-select { width: 180px; min-height: 42px; }
    .employee-holiday-stat, .employee-holiday-panel { border: 1px solid rgba(23,32,51,.1); border-radius: 16px; background: rgba(255,255,255,.96); box-shadow: 0 14px 35px rgba(28,37,65,.1); animation: holidayRise .55s ease both; }
    .employee-holiday-stat { padding: 18px; } .employee-holiday-stat small { display: block; color: #667085; font-weight: 800; } .employee-holiday-stat strong { display: block; font-size: 2rem; font-weight: 900; line-height: 1; }
    .employee-holiday-panel { padding: 22px; }
    .employee-holiday-panel-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
    .employee-holiday-panel-head h3 { margin: 0; font-weight: 900; } .employee-holiday-panel-head p { color: #667085; font-weight: 650; margin-bottom: 0; }
    .employee-selection-tools { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .employee-selection-tools span { display: inline-flex; align-items: center; min-height: 34px; padding: 6px 12px; border-radius: 999px; background: #ecfdf5; color: #047857; font-weight: 900; }
    .employee-day { display: inline-flex; padding: 7px 10px; border-radius: 999px; background: #eef2ff; color: #3730a3; font-weight: 800; }
    .employee-reason { font-weight: 900; }
    .employee-badge { display: inline-flex; padding: 7px 10px; border-radius: 999px; font-weight: 900; font-size: .8rem; }
    .employee-badge.weekly { background: #d1fae5; color: #047857; } .employee-badge.special { background: #dbeafe; color: #1d4ed8; }
    .employee-holiday-page .dataTables_wrapper .dt-buttons { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0 0 16px; padding: 8px; border: 1px solid rgba(16,185,129,.14); border-radius: 16px; background: #f7fcf9; }
    .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button { min-height: 42px; margin: 0 !important; padding: 9px 16px !important; border: 1px solid rgba(16,185,129,.18) !important; border-radius: 12px !important; background: #fff !important; color: #0f744c !important; box-shadow: 0 4px 12px -10px rgba(5,150,105,.7); font-size: .95rem !important; font-weight: 750 !important; line-height: 1.2 !important; transition: border-color .2s ease, background .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease; }
    .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button span { display: inline-flex; align-items: center; gap: 8px; }
    .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button span::before { display: inline-flex; align-items: center; justify-content: center; width: 20px; font-family: "Font Awesome 5 Free"; font-size: 1rem; font-weight: 900; }
    .employee-holiday-page .dataTables_wrapper .buttons-copy span::before { content: "\f0c5"; }
    .employee-holiday-page .dataTables_wrapper .buttons-csv span::before { content: "\f6dd"; }
    .employee-holiday-page .dataTables_wrapper .buttons-excel span::before { content: "\f1c3"; }
    .employee-holiday-page .dataTables_wrapper .buttons-pdf span::before { content: "\f1c1"; }
    .employee-holiday-page .dataTables_wrapper .buttons-print span::before { content: "\f02f"; }
    .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button:hover, .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button:focus { border-color: #10b981 !important; background: linear-gradient(145deg, #34d399, #059669) !important; color: #fff !important; box-shadow: 0 9px 20px -12px rgba(5,150,105,.8); transform: translateY(-1px); }
    .employee-holiday-page .dataTables_wrapper .buttons-excel { background: #ecfdf5 !important; }
    .employee-holiday-page .dataTables_wrapper .buttons-csv { background: #f0fdf4 !important; }
    .employee-holiday-page .dataTables_wrapper .buttons-pdf { color: #b91c1c !important; background: #fff7f7 !important; border-color: rgba(239,68,68,.18) !important; }
    .employee-holiday-page .dataTables_wrapper .buttons-print { color: #315f75 !important; background: #f4fbff !important; border-color: rgba(49,95,117,.18) !important; }
    html[data-pms-theme="dark"] .employee-holiday-page .dataTables_wrapper .dt-buttons { border-color: rgba(122,240,181,.16); background: #142a20; }
    html[data-pms-theme="dark"] .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button { border-color: rgba(122,240,181,.18) !important; background: #183026 !important; color: #d9f1e4 !important; }
    @keyframes holidayRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .employee-holiday-hero, .employee-holiday-panel-head { flex-direction: column; align-items: flex-start; } .employee-holiday-filter, .employee-holiday-filter > div, .employee-holiday-filter .form-select, .employee-holiday-filter .btn { width: 100%; } .employee-holiday-page .dataTables_wrapper .dt-buttons { width: 100%; } .employee-holiday-page .dataTables_wrapper .dt-buttons .dt-button { flex: 1 1 120px; justify-content: center; } }
</style>
@endsection

@push('js')
<script>
    $(function () {
        const employeeHolidayTable = $('#employeeHolidayTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copyHtml5', text: 'Copy', exportOptions: { columns: [1, 2, 3, 4] } },
                { extend: 'csvHtml5', text: 'CSV', filename: 'employee-holiday-list', exportOptions: { columns: [1, 2, 3, 4] } },
                { extend: 'excelHtml5', text: 'Excel', filename: 'employee-holiday-list', exportOptions: { columns: [1, 2, 3, 4] } },
                { extend: 'pdfHtml5', text: 'PDF', filename: 'employee-holiday-list', exportOptions: { columns: [1, 2, 3, 4] } },
                { extend: 'print', text: 'Print', exportOptions: { columns: [1, 2, 3, 4] } }
            ],
            responsive: true,
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [{ targets: 0, orderable: false, searchable: false }],
            language: { search: '_INPUT_', searchPlaceholder: 'Search by year, month, day, or reason...' }
        });

        const selectAll = $('#employeeHolidaySelectAll');
        const clearSelection = $('#employeeHolidayClearSelection');
        const selectedCount = $('#employeeHolidaySelectedCount');

        function filteredCheckboxes() {
            return $(employeeHolidayTable.rows({ search: 'applied' }).nodes()).find('.employee-holiday-checkbox');
        }

        function updateSelectionState() {
            const filtered = filteredCheckboxes();
            const selected = $('.employee-holiday-checkbox:checked');
            const filteredSelected = filtered.filter(':checked');
            const selectedTotal = selected.length;

            selectedCount.text(selectedTotal + (selectedTotal === 1 ? ' selected' : ' selected'));
            clearSelection.prop('disabled', selectedTotal === 0);
            selectAll.prop('checked', filtered.length > 0 && filteredSelected.length === filtered.length);
            selectAll.prop('indeterminate', filteredSelected.length > 0 && filteredSelected.length < filtered.length);
        }

        $('#employeeHolidayDayFilter').on('change', function () {
            employeeHolidayTable.column(2).search(this.value).draw();
        });

        $('#employeeHolidayTypeFilter').on('change', function () {
            employeeHolidayTable.column(4).search(this.value).draw();
        });

        selectAll.on('change', function () {
            filteredCheckboxes().prop('checked', this.checked);
            updateSelectionState();
        });

        $(document).on('change', '.employee-holiday-checkbox', updateSelectionState);

        clearSelection.on('click', function () {
            $('.employee-holiday-checkbox').prop('checked', false);
            updateSelectionState();
        });

        employeeHolidayTable.on('draw', updateSelectionState);
        updateSelectionState();

        function selectedRowsForSnapshot() {
            const selectedRows = $('.employee-holiday-checkbox:checked').map(function () {
                return {
                    date: $(this).data('date'),
                    day: $(this).data('day'),
                    title: $(this).data('title'),
                    type: $(this).data('type')
                };
            }).get();

            return selectedRows.length ? selectedRows : @json($snapshotRows);
        }

        $('#employeeHolidayScreenshot').on('click', function () {
            const rows = selectedRowsForSnapshot();
            const canvas = document.createElement('canvas');
            const width = 1200, rowHeight = 42, height = Math.max(420, 150 + rows.length * rowHeight);
            canvas.width = width; canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#f6f8fb'; ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#0f766e'; ctx.fillRect(0, 0, width, 100);
            ctx.fillStyle = '#fff'; ctx.font = 'bold 34px Arial'; ctx.fillText('Holiday Calendar {{ $selectedYear }}', 36, 60);
            ctx.fillStyle = '#172033'; ctx.font = 'bold 16px Arial';
            ['Date', 'Day', 'Reason', 'Type'].forEach((head, i) => ctx.fillText(head, [40, 190, 360, 980][i], 135));
            ctx.font = '14px Arial';
            rows.forEach((row, index) => {
                const y = 168 + index * rowHeight;
                ctx.fillStyle = index % 2 ? '#ffffff' : '#eef2ff'; ctx.fillRect(30, y - 24, 1140, 34);
                ctx.fillStyle = '#172033';
                ctx.fillText(row.date, 40, y);
                ctx.fillText(row.day, 190, y);
                ctx.fillText(String(row.title).slice(0, 70), 360, y);
                ctx.fillText(row.type, 980, y);
            });
            const link = document.createElement('a');
            link.download = 'employee-holiday-list-{{ $selectedYear }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });
</script>
@endpush
