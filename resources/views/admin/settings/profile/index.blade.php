@extends('admin.settings.layout')

@section('settings-content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="mb-1">Profile Settings</h5>
        <p class="text-muted mb-0">Manage profile fields and requirements.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn-close" aria-label="Close profile settings"></a>
</div>

<form method="POST" action="{{ route('admin.settings.profile.update') }}">
    @csrf
    @method('PUT')

    <div class="row g-4">

        @foreach($settings as $setting)
            <div class="col-md-6">

                {{-- TEXT / EMAIL / NUMBER --}}
                @if(in_array($setting->type, ['text','email','number']))
                    <label class="form-label fw-semibold">
                        {{ $setting->label }}
                        @if($setting->required)
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    <input
                        type="{{ $setting->type }}"
                        name="{{ $setting->key }}"
                        value="{{ old($setting->key, $setting->value) }}"
                        class="form-control"
                        {{ $setting->required ? 'required' : '' }}
                    />

                {{-- TEXTAREA --}}
                @elseif($setting->type === 'textarea')
                    <label class="form-label fw-semibold">{{ $setting->label }}</label>
                    <textarea
                        name="{{ $setting->key }}"
                        class="form-control"
                        rows="3"
                        {{ $setting->required ? 'required' : '' }}
                    >{{ old($setting->key, $setting->value) }}</textarea>

                            {{-- SELECT --}}
                @if($setting->type === 'select')
                    <label class="form-label">{{ $setting->label }}</label>

                    @php
                        $options = is_array($setting->options)
                            ? $setting->options
                            : json_decode($setting->options ?? '[]', true);
                    @endphp

                    <select
                        name="{{ $setting->key }}"
                        class="form-select"
                        {{ $setting->required ? 'required' : '' }}
                    >
                        @foreach($options as $option)
                            <option value="{{ $option }}"
                                {{ $setting->value == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
@endif

                {{-- CHECKBOX --}}
                @elseif($setting->type === 'checkbox')
                    <div class="form-check form-switch mt-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="{{ $setting->key }}"
                            value="1"
                            {{ $setting->value ? 'checked' : '' }}
                        >
                        <label class="form-check-label fw-semibold">
                            {{ $setting->label }}
                        </label>
                    </div>
                @endif

            </div>
        @endforeach
    </div>

    {{-- Action Buttons --}}
    <div class="mt-5 pt-4 border-top d-flex justify-content-between flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-2"></i>Save Profile Settings
            </button>
        </div>

        <button type="button"
                class="btn btn-outline-success"
                data-bs-toggle="modal"
                data-bs-target="#addProfileFieldModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Field
        </button>
    </div>
</form>

{{-- ADD FIELD MODAL --}}
<div class="modal fade" id="addProfileFieldModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.settings.profile.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Profile Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Key</label>
                        <input type="text" name="key" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Label</label>
                        <input type="text" name="label" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-select" id="fieldType">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="number">Number</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select</option>
                            <option value="checkbox">Checkbox</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="optionsBox">
                        <label>Options (comma separated)</label>
                        <textarea name="options" class="form-control"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="required" value="1">
                        <label class="form-check-label">Required</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success">Add Field</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- JS --}}
<script>
document.getElementById('fieldType').addEventListener('change', function () {
    document.getElementById('optionsBox')
        .classList.toggle('d-none', this.value !== 'select');
});
</script>

@endsection
