{{-- Unified Project Subnav Delegate --}}
@include('admin.projects.partials.header', [
    'project' => $project ?? $currentProject ?? null,
    'activeTab' => $activeTab ?? $active ?? 'overview'
])
