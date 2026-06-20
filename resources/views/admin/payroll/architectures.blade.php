@extends('admin.layout.app')

@section('title', 'Payroll Architectures')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">New Architecture</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.architectures.store') }}">
                        @csrf
                        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            @foreach($types as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" value="{{ $type }}" @checked($loop->first)>
                                    <label class="form-check-label">{{ Str::headline($type) }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-3"><label class="form-label">Effective Date</label><input type="date" name="effective_date" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                        <button class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Architectures</h5></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Type</th><th>Version</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse($architectures as $architecture)
                                <tr>
                                    <td>{{ $architecture->name }}</td>
                                    <td>{{ Str::headline($architecture->type) }}</td>
                                    <td>Version {{ $architecture->version }}</td>
                                    <td><span class="badge bg-label-{{ $architecture->is_active ? 'success' : 'secondary' }}">{{ $architecture->is_active ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="text-end">
                                        @unless($architecture->is_active)
                                            <form method="POST" action="{{ route('payroll.architectures.activate', $architecture) }}">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-sm btn-primary">Activate</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No payroll architectures found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
