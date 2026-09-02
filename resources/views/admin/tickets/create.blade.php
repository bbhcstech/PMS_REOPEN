@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark"><i class="bx bx-plus-circle text-primary me-2"></i> Create Support Ticket</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}" class="text-decoration-none">Tickets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Ticket</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <h6 class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i> Please fix the following errors:</h6>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">
            <!-- Left 8 Columns: Form Cards -->
            <div class="col-lg-8">
                <!-- Card 1: Requester & Client Info -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-user-pin text-primary me-2"></i> 1. Requester & Client Details</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Requester Category <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-4 p-2 bg-light rounded-3 border">
                                    <div class="form-check mb-0">
                                        <input type="radio" class="form-check-input" name="requester_type" id="requester-client" value="client" checked required>
                                        <label class="form-check-label fw-semibold" for="requester-client">Client / Product Owner</label>
                                    </div>
                                    <div class="form-check mb-0">
                                        <input type="radio" class="form-check-input" name="requester_type" id="requester-employee" value="employee" required>
                                        <label class="form-check-label fw-semibold" for="requester-employee">Employee / Internal</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="requester_name" class="form-label fw-semibold text-dark">Select Requester / Client Name <span class="text-danger">*</span></label>
                                <select name="requester_name" id="requester_name" class="form-select rounded-3" required>
                                    <option value="">-- Choose Requester --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" data-type="client">{{ $client->name }} (Client)</option>
                                    @endforeach
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" data-type="employee" style="display: none;">{{ $employee->name }} (Employee)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Project & Affected Part/Module -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-briefcase text-success me-2"></i> 2. Project & Affected Problem Area</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="project_id" class="form-label fw-semibold text-dark">Delivered Project / Product <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-select rounded-3" required>
                                    <option value="">-- Select Project --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="affected_module" class="form-label fw-semibold text-dark">Which Project Part / Module has the Issue?</label>
                                <input type="text" name="affected_module" id="affected_module" class="form-control rounded-3" placeholder="e.g. Checkout Page, Payment Gateway, API, Payroll" />
                                <small class="text-muted">Specify feature/module where problem occurred.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="ticket_type_id" class="form-label fw-semibold text-dark">Issue Classification / Type <span class="text-danger">*</span></label>
                                <select name="type_id" id="ticket_type_id" class="form-select rounded-3" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="1">Bug / Defect</option>
                                    <option value="2">Suggestion</option>
                                    <option value="3">Question</option>
                                    <option value="4">Sales Inquiry</option>
                                    <option value="5">Code Issue</option>
                                    <option value="6">Management</option>
                                    <option value="7">Critical Problem</option>
                                    <option value="8">Production Incident</option>
                                    <option value="9">Feature Enhancement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Issue Description & Error Sheet Upload -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-detail text-warning me-2"></i> 3. Issue Summary & Error Log Upload</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold text-dark">Ticket Subject / Title <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="subject" class="form-control rounded-3" placeholder="Short summary of the bug/problem reported by client" required />
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold text-dark">Detailed Description of Problem <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control rounded-3" rows="4" placeholder="Explain the exact issue, steps to reproduce, or client feedback..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="attachment" class="form-label fw-semibold text-dark">Attach Error Sheet / File for Developer (Excel, CSV, PDF, Screenshot)</label>
                            <input type="file" name="attachment" id="attachment" class="form-control rounded-3" />
                            <small class="text-muted">Attach client bug excel sheet, CSV log, screenshot or document for developer reference (max 10MB).</small>
                        </div>

                        <div class="mb-0">
                            <label for="tags" class="form-label fw-semibold text-dark">Tags (Optional)</label>
                            <input type="text" name="tags" id="tags" class="form-control rounded-3" placeholder="e.g. urgent, frontend, backend, database" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4 Columns: Assignment & Deadline -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px; z-index: 10;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-user-check text-success me-2"></i> 4. Assignment & Deadline</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="agent_id" class="form-label fw-semibold text-dark">Assign Developer / Employee <span class="text-danger">*</span></label>
                            <select name="agent_id" id="agent_id" class="form-select rounded-3" required>
                                <option value="">-- Select Developer --</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Assigned developer will get an instant notification & dashboard alert.</small>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label fw-semibold text-dark">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-select rounded-3" required>
                                <option value="low">Low Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="critical">Critical Priority</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="deadline" class="form-label fw-semibold text-dark">Target Resolution Deadline</label>
                            <input type="date" name="deadline" id="deadline" class="form-control rounded-3" value="{{ now()->addDays(2)->format('Y-m-d') }}" />
                            <small class="text-muted">Deadline for developer to complete the fix.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm">
                                <i class="bx bx-check-circle me-1"></i> Save & Dispatch Ticket
                            </button>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clientRadio = document.getElementById('requester-client');
        const employeeRadio = document.getElementById('requester-employee');
        const requesterSelect = document.getElementById('requester_name');

        function updateRequesterDropdown() {
            const isClient = clientRadio.checked;
            const targetType = isClient ? 'client' : 'employee';

            Array.from(requesterSelect.options).forEach(option => {
                if (!option.value) return;
                const optType = option.getAttribute('data-type');
                if (optType === targetType) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });

            requesterSelect.value = '';
        }

        if (clientRadio && employeeRadio && requesterSelect) {
            clientRadio.addEventListener('change', updateRequesterDropdown);
            employeeRadio.addEventListener('change', updateRequesterDropdown);
            updateRequesterDropdown();
        }
    });
</script>
@endpush
