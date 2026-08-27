@extends('admin.layout.app')

@section('content')
<div class="container">
    {{-- Standardized Project Header & 13-Tab Navigation --}}
    @include('admin.projects.partials.header', [
        'project' => $project,
        'activeTab' => 'overview'
    ])
    <h4>User Activities</h4>
    <ul class="list-group mb-4">
        @foreach($userActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}] 
                User ID {{ $activity->user_id }} — {{ $activity->activity }}
            </li>
        @endforeach
    </ul>

    <h4>Project Activities</h4>
    <ul class="list-group mb-4">
        @foreach($projectActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}] 
                Project ID {{ $activity->project_id }} — {{ $activity->activity }}
            </li>
        @endforeach
    </ul>

    <h4>Ticket Activities</h4>
    <ul class="list-group">
        @foreach($ticketActivities as $activity)
            <li class="list-group-item">
                [{{ $activity->created_at->format('d M Y H:i') }}]
                Ticket ID {{ $activity->ticket_id }} — 
                {{ $activity->type }}: {{ $activity->content }}
            </li>
        @endforeach
    </ul>
</div>
@endsection
@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-more');
    const moreTabs = document.getElementById('more-tabs');

    if (toggleBtn && moreTabs) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            moreTabs.classList.toggle('d-none');
            this.innerHTML = moreTabs.classList.contains('d-none') ? 'More ▾' : 'Less ▴';
        });
    }
});

</script>
@endpush