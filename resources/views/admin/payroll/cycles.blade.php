@extends('admin.layout.app')

@section('title', 'Payroll Cycles')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">New Cycle</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.cycles.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Cycle Name</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Type</label><select name="cycle_type" class="form-select">@foreach($cycleTypes as $type)<option value="{{ $type }}">{{ Str::headline($type) }}</option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Pay Date</label><input type="date" name="pay_date" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Lock Date</label><input type="date" name="lock_date" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <button class="btn btn-primary">Create Cycle</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Cycles</h5></div>
                <div class="table-responsive">
                    <table class="table"><thead><tr><th>Name</th><th>Type</th><th>Period</th><th>Pay Date</th><th>Status</th><th></th></tr></thead><tbody>
                        @forelse($cycles as $cycle)
                            <tr>
                                <td>{{ $cycle->name }}</td><td>{{ Str::headline($cycle->cycle_type) }}</td>
                                <td>{{ $cycle->start_date->format('d M Y') }} - {{ $cycle->end_date->format('d M Y') }}</td>
                                <td>{{ optional($cycle->pay_date)->format('d M Y') ?? '-' }}</td>
                                <td><span class="badge bg-label-primary">{{ ucfirst($cycle->status) }}</span></td>
                                <td><form method="POST" action="{{ route('payroll.cycles.process', $cycle) }}">@csrf<button class="btn btn-sm btn-primary">Generate Draft</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No payroll cycles found.</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
