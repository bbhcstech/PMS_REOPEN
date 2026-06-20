@extends('admin.layout.app')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">New {{ Str::singular($title) }}</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.' . $slug . '.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Rule Type</label><input name="rule_type" class="form-control" placeholder="custom"></div>
                        @if($slug === 'tax-rules')
                            <div class="mb-3"><label class="form-label">Country</label><select name="country" class="form-select"><option>India</option><option>USA</option><option>UK</option><option>Custom</option></select></div>
                            <div class="mb-3"><label class="form-label">Tax Slabs JSON</label><textarea name="slabs" class="form-control" rows="2"></textarea></div>
                            <div class="mb-3"><label class="form-label">Exemptions JSON</label><textarea name="exemptions" class="form-control" rows="2"></textarea></div>
                        @elseif($slug === 'overtime-rules')
                            <div class="mb-3"><label class="form-label">Multiplier</label><input type="number" step="0.01" name="multiplier" class="form-control" value="1"></div>
                        @else
                            <div class="mb-3"><label class="form-label">Calculation</label><select name="calculation_type" class="form-select"><option>fixed</option><option>percentage</option><option>formula</option><option>conditional</option></select></div>
                            <div class="mb-3"><label class="form-label">Value</label><input type="number" step="0.01" name="value" class="form-control"></div>
                        @endif
                        <div class="mb-3"><label class="form-label">Formula</label><textarea name="formula" class="form-control" rows="3" placeholder="IF GrossSalary > 50000 THEN 20 ELSE 10"></textarea></div>
                        <div class="mb-3"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control"></div>
                        <button class="btn btn-primary">Save Rule</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Code</th><th>Version</th><th>Status</th><th>Formula</th></tr></thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td>{{ $rule->name }}</td>
                                    <td>{{ $rule->code }}</td>
                                    <td>{{ $rule->version ?? 1 }}</td>
                                    <td><span class="badge bg-label-{{ $rule->is_active ? 'success' : 'secondary' }}">{{ $rule->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td>{{ Str::limit($rule->formula ?? '-', 60) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
