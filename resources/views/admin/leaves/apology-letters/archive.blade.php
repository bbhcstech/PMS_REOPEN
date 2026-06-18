@extends('admin.layout.app')

@section('title', $isAdmin ? 'Archived Employee Apology Letters' : 'My Archived Apology Letters')

@section('content')
<div class="leave-page">
    <div class="leave-breadcrumb"><i class="fas fa-box-archive"></i> Dashboard / Leaves / Archived Apology Letters</div>

    <section class="leave-hero">
        <div class="leave-hero-main">
            <div class="leave-hero-icon"><i class="fas fa-box-archive"></i></div>
            <div>
                <h1>{{ $isAdmin ? 'Archived Employee Apology Letters' : 'My Archived Apology Letters' }}</h1>
                <p>{{ $isAdmin ? 'Restore archived employee apology letters when HR needs them back in review.' : 'Restore your archived apology letters when you need them active again.' }}</p>
            </div>
        </div>
        <div class="leave-hero-actions">
            <a href="{{ route('leaves.apology-letters.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Active Letters</a>
            <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-calendar-days"></i> Leaves</a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="filter-panel">
        <form method="GET" action="{{ route('leaves.apology-letters.archive') }}" class="filter-grid">
            @if($isAdmin)
                <div>
                    <label>Employee</label>
                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Subject, body, employee">
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('leaves.apology-letters.archive') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </section>

    <section class="table-card">
        <div class="table-head">
            <div>
                <h2>Archived Letters</h2>
                <p>Select letters to restore them back to the active apology-letter inbox.</p>
            </div>
            <form method="POST" action="{{ route('leaves.apology-letters.bulk-restore') }}" id="apologyBulkRestoreForm" onsubmit="return confirm('Restore selected apology letter(s)?');">
                @csrf
                <button class="btn btn-primary" type="submit"><i class="fas fa-rotate-left"></i> Restore Selected</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table leave-table">
                <thead>
                    <tr>
                        <th class="checkbox-col"><input type="checkbox" class="form-check-input" id="selectAllArchivedApologyLetters"></th>
                        @if($isAdmin)<th>Employee</th>@endif
                        <th>Subject</th>
                        <th>Related Leave</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($letters as $letter)
                        <tr>
                            <td class="checkbox-col">
                                <input type="checkbox" class="form-check-input archived-apology-letter-checkbox" name="ids[]" value="{{ $letter->id }}" form="apologyBulkRestoreForm">
                            </td>
                            @if($isAdmin)
                                <td>
                                    <strong>{{ $letter->user?->name ?? 'N/A' }}</strong>
                                    <small>{{ $letter->user?->employeeDetail?->department?->dpt_name ?? 'Employee' }}</small>
                                </td>
                            @endif
                            <td>{{ $letter->subject }}</td>
                            <td>{{ $letter->leave ? ($letter->leave->type_label . ' - ' . optional($letter->leave->start_date)->format('d M Y')) : 'Not linked' }}</td>
                            <td><span class="status-badge archived">{{ ucfirst($letter->status) }}</span></td>
                            <td>{{ $letter->archived_at?->format('d M Y h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('leaves.apology-letters.show', $letter->id) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View</a>
                                <form method="POST" action="{{ route('leaves.apology-letters.restore', $letter->id) }}" class="d-inline" onsubmit="return confirm('Restore this apology letter?');">
                                    @csrf
                                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-rotate-left"></i> Restore</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-box-open"></i><h3>No archived apology letters found</h3></div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $letters->links() }}</div>
    </section>
</div>
@include('admin.leaves.apology-letters.styles')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllArchivedApologyLetters');
        const checkboxes = Array.from(document.querySelectorAll('.archived-apology-letter-checkbox'));
        const bulkForm = document.getElementById('apologyBulkRestoreForm');

        selectAll?.addEventListener('change', () => {
            checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
        });

        bulkForm?.addEventListener('submit', event => {
            if (!checkboxes.some(checkbox => checkbox.checked)) {
                event.preventDefault();
                alert('Please select at least one archived apology letter.');
            }
        });
    });
</script>
@endsection
