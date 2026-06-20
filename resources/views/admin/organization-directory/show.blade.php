@extends('admin.layout.app')

@section('title', $employee->name)

@section('content')
@php
    $detail = $employee->employeeDetail;
    $avatar = $employee->profile_image ? asset($employee->profile_image) : null;
    $dob = $detail?->dob ?? $employee->dob;
    $joiningDate = $detail?->joining_date ?? $employee->joining_date;
    $mobile = $employee->mobile ?: $detail?->mobile;
    $publicAbout = $detail?->directory_about ?: ($detail?->about ?: $employee->about);
    $profileLinks = array_filter([
        'LinkedIn' => $detail?->linkedin_url,
        'Portfolio' => $detail?->portfolio_url,
        'Facebook' => $detail?->facebook_url,
        'Instagram' => $detail?->instagram_url,
        'X / Twitter' => $detail?->x_url,
        'CV / Resume' => $detail?->cv_path ? asset($detail->cv_path) : null,
    ]);
    $canManageDirectory = in_array(auth()->user()?->role, ['admin', 'hr'], true);
@endphp

<div class="org-page">
    @if(session('success'))
        <div class="alert alert-success org-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger org-alert">Please fix the highlighted directory details.</div>
    @endif

    <section class="org-profile-hero">
        <div class="org-profile-main">
            <div class="org-profile-avatar">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="{{ $employee->name }}">
                @else
                    <span>{{ strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <span class="org-eyebrow"><i class="fas fa-user-tie"></i> Employee Profile</span>
                <h1>{{ $employee->name }}</h1>
                <p>{{ $detail?->designation?->name ?? $employee->designation ?? 'Team Member' }}</p>
            </div>
        </div>
        <div class="org-hero-actions">
            @if($canManageDirectory)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary"><i class="fas fa-user-pen"></i> Full Employee Edit</a>
            @endif
            <a href="{{ route('organization.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Directory</a>
        </div>
    </section>

    <section class="org-profile-grid">
        <article class="org-panel">
            <h2>Organization Details</h2>
            <dl class="org-detail-list">
                <dt>Employee ID</dt><dd>{{ $detail?->employee_id ?? 'Not added' }}</dd>
                <dt>Department</dt><dd>{{ $detail?->department?->dpt_name ?? 'Not assigned' }}</dd>
                <dt>Parent Department</dt><dd>{{ $detail?->department?->parent?->dpt_name ?? 'Not assigned' }}</dd>
                <dt>Designation</dt><dd>{{ $detail?->designation?->name ?? $employee->designation ?? 'Not assigned' }}</dd>
                <dt>Employment Type</dt><dd>{{ $detail?->employment_type ?? 'Not added' }}</dd>
                <dt>Status</dt><dd><span class="org-status">{{ $detail?->status ?? 'Active' }}</span></dd>
                <dt>Reporting Manager</dt><dd>{{ $detail?->reportingTo?->name ?? 'Not assigned' }}</dd>
            </dl>
        </article>

        <article class="org-panel">
            <h2>Contact</h2>
            <div class="org-contact-list">
                <a href="mailto:{{ $employee->email }}"><i class="fas fa-envelope"></i>{{ $employee->email }}</a>
                @if($mobile)
                    <a href="tel:{{ preg_replace('/\s+/', '', $mobile) }}"><i class="fas fa-phone"></i>{{ $mobile }}</a>
                @else
                    <span><i class="fas fa-phone"></i>Phone not added</span>
                @endif
                @if($employee->slack_id)
                    <span><i class="fab fa-slack"></i>{{ $employee->slack_id }}</span>
                @endif
            </div>
        </article>
    </section>

    <section class="org-profile-grid">
        <article class="org-panel">
            <h2>Personal Work Info</h2>
            <dl class="org-detail-list">
                <dt>Birthday</dt><dd>{{ $dob ? \Carbon\Carbon::parse($dob)->format('d M') : 'Not added' }}</dd>
                <dt>Joining Date</dt><dd>{{ $joiningDate ? \Carbon\Carbon::parse($joiningDate)->format('d M Y') : 'Not added' }}</dd>
                <dt>Gender</dt><dd>{{ $detail?->gender ?? $employee->gender ?? 'Not added' }}</dd>
                <dt>Country</dt><dd>{{ $detail?->country ?? $employee->country ?? 'Not added' }}</dd>
                <dt>Language</dt><dd>{{ $detail?->language ?? $employee->language ?? 'Not added' }}</dd>
            </dl>
        </article>

        <article class="org-panel">
            <h2>About & Skills</h2>
            <p class="org-about">{{ $publicAbout ?: 'No about details added yet.' }}</p>
            @if($detail?->skills)
                <div class="org-skill-list">
                    @foreach(array_filter(array_map('trim', explode(',', $detail->skills))) as $skill)
                        <span>{{ $skill }}</span>
                    @endforeach
                </div>
            @else
                <p class="org-muted">No skills added yet.</p>
            @endif
        </article>
    </section>

    <section class="org-panel">
        <h2>Professional Links</h2>
        @if($profileLinks)
            <div class="org-profile-links">
                @foreach($profileLinks as $label => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener">
                        <i class="{{ $label === 'LinkedIn' ? 'fab fa-linkedin' : ($label === 'Facebook' ? 'fab fa-facebook' : ($label === 'Instagram' ? 'fab fa-instagram' : ($label === 'X / Twitter' ? 'fab fa-x-twitter' : ($label === 'CV / Resume' ? 'fas fa-file-lines' : 'fas fa-globe')))) }}"></i>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @else
            <p class="org-muted">No professional links added yet.</p>
        @endif
    </section>

    @if($canManageDirectory)
        <section class="org-editor-panel" id="directory-editor">
            <div class="org-editor-head">
                <div>
                    <span class="org-eyebrow"><i class="fas fa-pen-to-square"></i> Admin / HR</span>
                    <h2>Add Or Override Directory Details</h2>
                    <p>These fields update this employee's public Organization profile. Base details still come automatically from the Employee section.</p>
                </div>
            </div>

            <div class="org-source-summary">
                <div>
                    <span>Employee</span>
                    <strong>{{ $employee->name }}</strong>
                </div>
                <div>
                    <span>Department</span>
                    <strong>{{ $detail?->department?->dpt_name ?? 'Not assigned' }}</strong>
                </div>
                <div>
                    <span>Designation</span>
                    <strong>{{ $detail?->designation?->name ?? $employee->designation ?? 'Not assigned' }}</strong>
                </div>
                <div>
                    <span>Employee ID</span>
                    <strong>{{ $detail?->employee_id ?? 'Not added' }}</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('organization.directory-profile.update', $employee) }}" enctype="multipart/form-data" class="org-editor-form">
                @csrf
                @method('PATCH')

                <div class="org-form-full">
                    <label>Public About / HR Rewrite</label>
                    <textarea name="directory_about" rows="4" class="form-control" placeholder="Write the professional profile employees should see in the Organization section.">{{ old('directory_about', $detail?->directory_about) }}</textarea>
                    @error('directory_about')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="org-form-full">
                    <label>Skills</label>
                    <textarea name="skills" rows="2" class="form-control" placeholder="Comma separated skills, e.g. Laravel, CRM, Operations">{{ old('skills', $detail?->skills) }}</textarea>
                    @error('skills')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>LinkedIn</label>
                    <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $detail?->linkedin_url) }}" placeholder="https://linkedin.com/in/name">
                    @error('linkedin_url')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>Portfolio</label>
                    <input type="url" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $detail?->portfolio_url) }}" placeholder="https://portfolio.example">
                    @error('portfolio_url')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>Facebook</label>
                    <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $detail?->facebook_url) }}" placeholder="https://facebook.com/name">
                    @error('facebook_url')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>Instagram</label>
                    <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $detail?->instagram_url) }}" placeholder="https://instagram.com/name">
                    @error('instagram_url')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>X / Twitter</label>
                    <input type="url" name="x_url" class="form-control" value="{{ old('x_url', $detail?->x_url) }}" placeholder="https://x.com/name">
                    @error('x_url')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div>
                    <label>CV / Resume</label>
                    <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx,application/pdf">
                    @if($detail?->cv_path)
                        <small class="org-file-note">Current: <a href="{{ asset($detail->cv_path) }}" target="_blank" rel="noopener">view CV</a></small>
                    @endif
                    @error('cv_file')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="org-editor-actions">
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Save Directory Details</button>
                </div>
            </form>
        </section>
    @endif
</div>

@include('admin.organization-directory.styles')
@endsection
