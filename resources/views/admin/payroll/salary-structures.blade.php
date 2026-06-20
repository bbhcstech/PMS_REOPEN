@extends('admin.layout.app')

@section('title', 'Salary Structures')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">New Structure</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.salary-structures.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <button class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Add Component</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.salary-components.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Structure</label><select name="salary_structure_id" class="form-select"><option value="">Global</option>@foreach($structures as $structure)<option value="{{ $structure->id }}">{{ $structure->name }}</option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Type</label><select name="component_type" class="form-select"><option>allowance</option><option>basic</option><option>bonus</option><option>custom</option></select></div>
                        <div class="mb-3"><label class="form-label">Calculation</label><select name="calculation_type" class="form-select"><option>fixed</option><option>percentage</option><option>formula</option></select></div>
                        <div class="mb-3"><label class="form-label">Value</label><input type="number" step="0.01" name="value" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Formula</label><textarea name="formula" class="form-control" rows="2"></textarea></div>
                        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="taxable" value="1"><label class="form-check-label">Taxable</label></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="required" value="1"><label class="form-check-label">Required</label></div>
                        <button class="btn btn-primary">Add Component</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Structures</h5></div>
                <div class="table-responsive">
                    <table class="table"><thead><tr><th>Name</th><th>Code</th><th>Version</th><th>Components</th><th>Status</th></tr></thead><tbody>
                        @forelse($structures as $structure)
                            <tr><td>{{ $structure->name }}</td><td>{{ $structure->code }}</td><td>{{ $structure->version }}</td><td>{{ $structure->components_count }}</td><td><span class="badge bg-label-success">Active</span></td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No salary structures found.</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Components</h5></div>
                <div class="table-responsive">
                    <table class="table"><thead><tr><th>Name</th><th>Structure</th><th>Type</th><th>Calculation</th><th>Taxable</th><th>Required</th></tr></thead><tbody>
                        @forelse($components as $component)
                            <tr><td>{{ $component->name }}</td><td>{{ $component->salaryStructure?->name ?? 'Global' }}</td><td>{{ $component->component_type }}</td><td>{{ $component->calculation_type }}</td><td>{{ $component->taxable ? 'Yes' : 'No' }}</td><td>{{ $component->required ? 'Yes' : 'No' }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No salary components found.</td></tr>
                        @endforelse
                    </tbody></table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
