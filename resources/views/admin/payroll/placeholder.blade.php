@extends('admin.layout.app')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div>
        <div class="card-body">
            <p class="text-muted mb-0">This payroll feature is registered in Module Management and ready for workflow, visibility, and permission configuration.</p>
        </div>
    </div>
</div>
@endsection
