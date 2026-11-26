@if(\App\Support\WorkforceSettings::active())
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-sm">
            Back to Settings
        </a>
        <span class="text-muted small">Organization Settings</span>
    </div>
@endif
