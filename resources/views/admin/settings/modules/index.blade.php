@extends('admin.layout.app')

@section('title', 'Module Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">Module Management</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Create Module</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.modules.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3"><label class="form-label">Module Name</label><input name="name" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Module Icon</label><input name="icon" class="form-control" placeholder="bx bx-cube"></div>
                <div class="col-md-3"><label class="form-label">Route</label><input name="route_name" class="form-control" placeholder="projects.index"></div>
                <div class="col-md-2"><label class="form-label">Route Prefix</label><input name="route_prefix" class="form-control" placeholder="projects"></div>
                <div class="col-md-2"><label class="form-label">Parent Module</label><select name="parent_id" class="form-select"><option value="">None</option>@foreach($parentModules as $parent)<option value="{{ $parent->id }}">{{ $parent->name }}</option>@endforeach</select></div>
                <div class="col-md-8"><label class="form-label">Description</label><input name="description" class="form-control"></div>
                <div class="col-md-2"><label class="form-label">Sort</label><input name="sort_order" type="number" class="form-control" value="0"></div>
                <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="moduleActive"><label class="form-check-label" for="moduleActive">Active</label></div></div>
                <div class="col-12"><button class="btn btn-primary">Create Module</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Name</th><th>Icon</th><th>Route</th><th>Parent</th><th>Status</th><th>Description</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                @foreach($modules as $module)
                    <tr>
                        <form method="POST" action="{{ route('admin.modules.update', $module) }}">
                            @csrf
                            @method('PUT')
                            <td><input name="name" class="form-control" value="{{ $module->name }}" required><input type="hidden" name="slug" value="{{ $module->slug }}"></td>
                            <td><input name="icon" class="form-control" value="{{ $module->icon }}"></td>
                            <td><input name="route_name" class="form-control" value="{{ $module->route_name }}"><input name="route_prefix" class="form-control mt-2" value="{{ $module->route_prefix }}"></td>
                            <td><select name="parent_id" class="form-select"><option value="">None</option>@foreach($parentModules->where('id', '!=', $module->id) as $parent)<option value="{{ $parent->id }}" @selected($module->parent_id === $parent->id)>{{ $parent->name }}</option>@endforeach</select></td>
                            <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($module->is_active)></div><input name="sort_order" type="number" class="form-control mt-2" value="{{ $module->sort_order }}"></td>
                            <td><input name="description" class="form-control" value="{{ $module->description }}"></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary">Save</button>
                        </form>
                                @unless($module->is_core)
                                    <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" class="d-inline" onsubmit="return confirm('Delete this module?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endunless
                            </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
