@extends('admin.layout.app')

@section('title', $isAdmin ? 'Employee Apology Letters' : 'My Apology Letters')

@section('content')
<div class="leave-page">
    <div class="leave-breadcrumb"><i class="fas fa-envelope-open-text"></i> Dashboard / Leaves / Apology Letters</div>

    <section class="leave-hero">
        <div class="leave-hero-main">
            <div class="leave-hero-icon"><i class="fas fa-envelope-open-text"></i></div>
            <div>
                <h1>{{ $isAdmin ? 'Employee Apology Letters' : 'My Apology Letters' }}</h1>
                <p>{{ $isAdmin ? 'Review apology letters submitted by employees one by one.' : 'Write, send, and track apology letters sent to HR.' }}</p>
            </div>
        </div>
        <div class="leave-hero-actions">
            @if(! $isAdmin)
                <a href="{{ route('leaves.apology-letters.create') }}" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Write Letter</a>
            @endif
            <a href="{{ route('leaves.apology-letters.archive') }}" class="btn btn-light">
                <i class="fas fa-box-archive"></i> Archived
                @if(($archivedCount ?? 0) > 0)
                    <span class="archive-count-badge">{{ $archivedCount }}</span>
                @endif
            </a>
            <a href="{{ route('leaves.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Leaves</a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isAdmin)
        <section class="filter-panel">
            <form method="GET" action="{{ route('leaves.apology-letters.index') }}" class="filter-grid">
                <div>
                    <label>Employee</label>
                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        @foreach(['submitted', 'reviewed', 'archived'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    <a href="{{ route('leaves.apology-letters.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                </div>
            </form>
        </section>
    @endif

    <section class="table-card">
        <div class="table-head">
            <div>
                <h2>Letters</h2>
                <p>{{ $isAdmin ? 'Open a letter to read it fully and mark it reviewed.' : 'HR can see each submitted letter from this inbox.' }}</p>
            </div>
            <form method="POST" action="{{ route('leaves.apology-letters.bulk-archive') }}" id="apologyBulkArchiveForm" onsubmit="return confirm('Archive selected apology letter(s)?');">
                @csrf
                <button class="btn btn-secondary" type="submit"><i class="fas fa-box-archive"></i> Archive Selected</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table leave-table">
                <thead>
                    <tr>
                        <th class="checkbox-col"><input type="checkbox" class="form-check-input" id="selectAllApologyLetters"></th>
                        @if($isAdmin)<th>Employee</th>@endif
                        <th>Subject</th>
                        <th>Related Leave</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($letters as $letter)
                        <tr>
                            <td class="checkbox-col">
                                <input type="checkbox" class="form-check-input apology-letter-checkbox" name="ids[]" value="{{ $letter->id }}" form="apologyBulkArchiveForm">
                            </td>
                            @if($isAdmin)
                                <td>
                                    <strong>{{ $letter->user?->name ?? 'N/A' }}</strong>
                                    <small>{{ $letter->user?->employeeDetail?->department?->dpt_name ?? 'Employee' }}</small>
                                </td>
                            @endif
                            <td>{{ $letter->subject }}</td>
                            <td>{{ $letter->leave ? ($letter->leave->type_label . ' - ' . optional($letter->leave->start_date)->format('d M Y')) : 'Not linked' }}</td>
                            <td><span class="status-badge {{ $letter->status === 'submitted' ? 'pending' : ($letter->status === 'archived' ? 'archived' : 'approved') }}">{{ ucfirst($letter->status) }}</span></td>
                            <td>{{ $letter->created_at?->format('d M Y h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('leaves.apology-letters.show', $letter->id) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View</a>
                                <form method="POST" action="{{ route('leaves.apology-letters.archive.action', $letter->id) }}" class="d-inline" onsubmit="return confirm('Archive this apology letter?');">
                                    @csrf
                                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-box-archive"></i> Archive</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center py-5">
                                <div class="empty-state"><i class="fas fa-envelope"></i><h3>No apology letters found</h3></div>
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
        const selectAll = document.getElementById('selectAllApologyLetters');
        const checkboxes = Array.from(document.querySelectorAll('.apology-letter-checkbox'));
        const bulkForm = document.getElementById('apologyBulkArchiveForm');

        selectAll?.addEventListener('change', () => {
            checkboxes.forEach(checkbox => checkbox.checked = selectAll.checked);
        });

        bulkForm?.addEventListener('submit', event => {
            if (!checkboxes.some(checkbox => checkbox.checked)) {
                event.preventDefault();
                alert('Please select at least one apology letter.');
            }
        });
    });
</script>
@endsection
