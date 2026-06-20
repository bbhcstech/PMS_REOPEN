@extends('admin.layout.app')

@section('title', 'Organization Directory')

@section('content')
@php
    $avatarUrl = function ($employee) {
        if ($employee->profile_image) {
            return asset($employee->profile_image);
        }

        return null;
    };
    $canManageEmployees = in_array(auth()->user()?->role, ['admin', 'hr'], true);
@endphp

<div class="org-page">
    <section class="org-hero">
        <div>
            <span class="org-eyebrow"><i class="fas fa-sitemap"></i> Organization</span>
            <h1>Employee Directory</h1>
            <p>Explore active team members, departments, designations, birthdays, and reporting details across the organization.</p>
        </div>
        <div class="org-hero-actions">
            @if($canManageEmployees)
                <a href="{{ route('employees.create') }}" class="btn btn-primary org-add-employee-btn">
                    <i class="fas fa-user-plus"></i> Add Employee Details
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </section>

    @if($canManageEmployees)
        <section class="org-admin-strip">
            <div>
                <span><i class="fas fa-briefcase"></i> Admin / HR Workspace</span>
                <strong>Add new employees or maintain public directory details from this section.</strong>
            </div>
            <div class="org-admin-actions">
                <!-- <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Employee Details</a> -->
                <a href="{{ route('employees.index') }}" class="btn btn-light"><i class="fas fa-users-gear"></i> Manage Employees</a>
            </div>
        </section>
    @endif

    <section class="org-stats">
        <div><span>Employees</span><strong>{{ $stats['employees'] }}</strong></div>
        <div><span>Departments</span><strong>{{ $stats['departments'] }}</strong></div>
        <div><span>Designations</span><strong>{{ $stats['designations'] }}</strong></div>
    </section>

    <section class="org-filter">
        <form method="GET" action="{{ route('organization.index') }}">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search name, email, employee ID, skills">
            <select name="department_id" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->dpt_name }}</option>
                @endforeach
            </select>
            <select name="designation_id" class="form-select">
                <option value="">All Designations</option>
                @foreach($designations as $designation)
                    <option value="{{ $designation->id }}" @selected((string) request('designation_id') === (string) $designation->id)>{{ $designation->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('organization.index') }}" class="btn btn-light"><i class="fas fa-rotate-left"></i> Reset</a>
        </form>
    </section>

    <section class="org-grid">
        @forelse($employees as $employee)
            @php
                $detail = $employee->employeeDetail;
                $avatar = $avatarUrl($employee);
                $publicAbout = $detail?->directory_about ?: ($detail?->about ?: $employee->about);
                $publicLinks = array_filter([
                    'linkedin' => $detail?->linkedin_url,
                    'portfolio' => $detail?->portfolio_url,
                    'facebook' => $detail?->facebook_url,
                    'instagram' => $detail?->instagram_url,
                    'x' => $detail?->x_url,
                    'cv' => $detail?->cv_path ? asset($detail->cv_path) : null,
                ]);
            @endphp
            <article class="org-card">
                <div class="org-card-top">
                    <a href="{{ route('organization.show', $employee) }}" class="org-avatar" aria-label="View {{ $employee->name }}">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $employee->name }}">
                        @else
                            <span>{{ strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
                        @endif
                    </a>
                    <div>
                        <h2>{{ $employee->name }}</h2>
                        <p>{{ $detail?->designation?->name ?? $employee->designation ?? 'Team Member' }}</p>
                    </div>
                </div>

                <div class="org-tags">
                    <span><i class="fas fa-building"></i>{{ $detail?->department?->dpt_name ?? 'Department not set' }}</span>
                    @if($detail?->employee_id)
                        <span><i class="fas fa-id-badge"></i>{{ $detail->employee_id }}</span>
                    @endif
                </div>

                <dl class="org-card-facts">
                    <dt>Birthday</dt>
                    <dd>{{ $detail?->dob?->format('d M') ?? ($employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M') : 'Not added') }}</dd>
                    <dt>Joined</dt>
                    <dd>{{ $detail?->joining_date?->format('d M Y') ?? ($employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : 'Not added') }}</dd>
                    <dt>Reports To</dt>
                    <dd>{{ $detail?->reportingTo?->name ?? 'Not assigned' }}</dd>
                </dl>

                @if($publicAbout)
                    <p class="org-card-about">{{ \Illuminate\Support\Str::limit($publicAbout, 135) }}</p>
                @endif

                @if($publicLinks)
                    <div class="org-social-strip">
                        @foreach([
                            'linkedin' => ['LinkedIn', 'fab fa-linkedin'],
                            'portfolio' => ['Portfolio', 'fas fa-globe'],
                            'facebook' => ['Facebook', 'fab fa-facebook'],
                            'instagram' => ['Instagram', 'fab fa-instagram'],
                            'x' => ['X', 'fab fa-x-twitter'],
                            'cv' => ['CV', 'fas fa-file-lines'],
                        ] as $key => [$label, $icon])
                            @if(!empty($publicLinks[$key]))
                                <a href="{{ $publicLinks[$key] }}" target="_blank" rel="noopener" title="{{ $label }}"><i class="{{ $icon }}"></i></a>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="org-card-links">
                    <a href="mailto:{{ $employee->email }}"><i class="fas fa-envelope"></i>Email</a>
                    @if($employee->mobile || $detail?->mobile)
                        <a href="tel:{{ preg_replace('/\s+/', '', $employee->mobile ?: $detail->mobile) }}"><i class="fas fa-phone"></i>Call</a>
                    @endif
                    <a href="{{ route('organization.show', $employee) }}"><i class="fas fa-eye"></i>View Details</a>
                    @if($canManageEmployees)
                        <a href="{{ route('organization.show', $employee) }}#directory-editor" class="org-card-edit-link"><i class="fas fa-pen-to-square"></i>Add/Edit Details</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="org-empty">
                <i class="fas fa-users-slash"></i>
                <h2>No active employees found</h2>
                <p>Try changing the filter or search terms.</p>
                @if($canManageEmployees)
                    <a href="{{ route('employees.create') }}" class="btn btn-primary mt-3"><i class="fas fa-user-plus"></i> Add Employee Details</a>
                @endif
            </div>
        @endforelse
    </section>

    <div class="org-pagination">{{ $employees->links() }}</div>

    <section class="org-structure">
        <div class="org-section-title">
            <span>Structure</span>
            <h2>Departments In The Organization</h2>
        </div>

        <div class="org-department-grid">
            @forelse($departmentGroups as $department)
                @php
                    $activeDetails = $department->employeeDetails->filter(fn ($detail) => $detail->status === 'Active' && $detail->user);
                @endphp
                <article class="org-department">
                    <div>
                        <h3>{{ $department->dpt_name }}</h3>
                        <p>{{ $department->parent?->dpt_name ?? 'Main department' }}</p>
                    </div>
                    <strong>{{ $activeDetails->count() }}</strong>
                    <div class="org-mini-list">
                        @foreach($activeDetails->take(4) as $detail)
                            <a href="{{ route('organization.show', $detail->user) }}">
                                <span>{{ strtoupper(mb_substr($detail->user->name, 0, 1)) }}</span>
                                <div>
                                    <b>{{ $detail->user->name }}</b>
                                    <small>{{ $detail->designation?->name ?? 'Team Member' }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="org-empty">
                    <i class="fas fa-building-circle-exclamation"></i>
                    <h2>No departments found</h2>
                    <p>Departments will appear here once active employees are assigned.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

@include('admin.organization-directory.styles')
@endsection
