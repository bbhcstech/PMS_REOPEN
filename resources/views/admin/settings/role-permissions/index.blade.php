@extends('admin.layout.app')

@section('title', 'Role & Permission')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Role & Permission</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.role-permissions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Select Role</label>
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        @foreach($roles as $option)
                            <option value="{{ $option }}" @selected($role === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.role-permissions.update') }}">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            @foreach($permissions as $permission)
                                <th class="text-center">{{ ucfirst($permission) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            @php $saved = $savedPermissions->get($module->id); @endphp
                            <tr>
                                <td>
                                    <strong>{{ $module->name }}</strong>
                                    @if($module->parent)<div class="text-muted small">{{ $module->parent->name }}</div>@endif
                                </td>
                                @foreach($permissions as $permission)
                                    <td class="text-center">
                                        <input type="checkbox" name="permissions[{{ $module->id }}][]" value="{{ $permission }}" @checked($role === 'admin' || (bool) optional($saved)->{'can_' . $permission})>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Save Permissions</button>
            </div>
        </div>
    </form>
</div>
@endsection
