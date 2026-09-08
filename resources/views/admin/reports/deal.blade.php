@extends('admin.layout.app')

@section('title', 'Deal Report')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase-alt text-primary me-2"></i> Deal Report</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Deal Report</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Total Client Deals / Contracts</small>
                <h3 class="fw-extrabold text-primary mb-0">{{ $deals->count() }}</h3>
            </div>
        </div>

        <div class="col-lg-6 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-white h-100 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold mb-1">Total Pipeline Deal Value</small>
                <h3 class="fw-extrabold text-success mb-0">${{ number_format($totalValue, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="dealTable" class="table table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Deal Title</th>
                            <th>Client Contact</th>
                            <th>Deal Value</th>
                            <th>Stage / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deals as $key => $deal)
                            <tr>
                                <td class="fw-bold">#{{ $key + 1 }}</td>
                                <td><div class="fw-bold text-dark">{{ $deal->title ?? $deal->subject ?? 'Client Deal' }}</div></td>
                                <td>{{ $deal->leadContact->client_name ?? $deal->client->name ?? 'Client' }}</td>
                                <td><span class="badge bg-success-subtle text-success fw-bold fs-6 px-3 py-1">${{ number_format($deal->value ?? $deal->amount ?? 0, 2) }}</span></td>
                                <td><span class="badge bg-primary rounded-pill px-3 py-1">{{ ucfirst($deal->stage->name ?? $deal->status ?? 'Active') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .dt-buttons .btn { border-radius: 50rem !important; padding: 0.35rem 0.9rem !important; font-size: 0.82rem !important; font-weight: 600 !important; margin-right: 0.35rem !important; background: #ffffff !important; border: 1px solid #e5e7eb !important; color: #374151 !important; }
    .dt-buttons .btn:hover { background: #e4f3eb !important; color: #0f744c !important; }
    .dataTables_filter input { border-radius: 50rem !important; padding: 0.35rem 1rem !important; border: 1px solid #d1d5db !important; }
</style>
@endpush

@push('js')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.3.0-beta.1/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        $('#dealTable').DataTable({
            dom: '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"Bf>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3"ip>',
            buttons: [
                { extend: 'copy', className: 'btn', text: '<i class="bx bx-copy me-1"></i> Copy' },
                { extend: 'csv', className: 'btn', text: '<i class="bx bx-file me-1"></i> CSV' },
                { extend: 'excel', className: 'btn', text: '<i class="bx bx-spreadsheet me-1"></i> Excel' },
                { extend: 'pdf', className: 'btn', text: '<i class="bx bxs-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn', text: '<i class="bx bx-printer me-1"></i> Print' }
            ],
            pageLength: 25
        });
    });
</script>
@endpush
