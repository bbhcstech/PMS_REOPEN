@extends('admin.layout.app')

@section('content')
<div class="container">
    <br>
    {{-- Standardized Project Header & 13-Tab Navigation --}}
    @include('admin.projects.partials.header', [
        'project' => $project,
        'activeTab' => 'notes'
    ])

    <div class="card">
        <div class="card-header">
            <h4>{{ $project->name }}</h4>
        </div>
        <div class="card-body">
            <p><strong>Client:</strong> {{ optional($project->client)->name ?? 'N/A' }}</p>
            <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</p>
            <p><strong>Deadline:</strong> {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($project->status) }}</p>

            <hr>

            <p><strong>Description:</strong></p>
            <div>{!! nl2br(e($project->description)) !!}</div>
        </div>
        <div class="card-footer text-end">
            <small>Created at: {{ $project->created_at->format('d M Y, h:i A') }}</small><br>
            <small>Last updated: {{ $project->updated_at->format('d M Y, h:i A') }}</small>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.getElementById('toggle-more').addEventListener('click', function(e) {
        e.preventDefault();
        const moreTabs = document.getElementById('more-tabs');
        if (moreTabs.classList.contains('d-none')) {
            moreTabs.classList.remove('d-none');
            this.innerHTML = 'Less ▴';
        } else {
            moreTabs.classList.add('d-none');
            this.innerHTML = 'More ▾';
        }
    });
</script>
@endpush
