@extends('admin.layout.app')

@section('title', $employee->name . ' - Employee Directory Profile')

@section('content')
@php
    $detail = $employee->employeeDetail;
    $status = strtolower($detail?->status ?? 'active');
    $avatar = $employee->profile_image ? asset($employee->profile_image) : null;
    $initials = strtoupper(mb_substr($employee->name, 0, 1));
    $dob = $detail?->dob ?? $employee->dob;
    $joiningDate = $detail?->joining_date ?? $employee->joining_date;
    $mobile = $employee->mobile ?: $detail?->mobile;
    $publicAbout = $detail?->directory_about ?: ($detail?->about ?: $employee->about);
    $designationName = $detail?->designation?->name ?? $employee->designation ?? 'Team Member';
    $deptName = $detail?->department?->dpt_name ?? 'Unassigned Department';
    $parentDeptName = $detail?->department?->parent?->dpt_name;
    $empCode = $detail?->employee_id ?: 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT);
    $companyName = $employee->company?->name ?? 'Primary Org';
    $managerName = $detail?->reportingTo?->name ?? 'Not assigned';

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
    <!-- Breadcrumb -->
    <div class="org-breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i> Dashboard</a>
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
        <a href="{{ route('organization.index') }}">Organization</a>
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
        <span>Employees</span>
        <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
        <span class="text-dark font-semibold">{{ $employee->name }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-xl py-3 px-4 mb-4 fw-bold text-emerald-800 bg-emerald-50">
            <i class="fas fa-check-circle me-2 text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-xl py-3 px-4 mb-4 fw-bold text-rose-800 bg-rose-50">
            <i class="fas fa-exclamation-triangle me-2 text-rose-600"></i> Please fix the highlighted directory details.
        </div>
    @endif

    <!-- Profile Executive Hero Banner -->
    <div class="org-profile-banner" style="background: linear-gradient(135deg, #094c32 0%, #0f744c 65%, #146c47 100%) !important; color: #ffffff !important;">
        <div class="org-banner-content">
            <div class="org-profile-user-group">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="{{ $employee->name }}" class="org-banner-avatar">
                @else
                    <div class="org-banner-avatar" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">{{ $initials }}</div>
                @endif
                <div class="org-banner-info">
                    <h1 style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; text-shadow: 0 2px 6px rgba(0,0,0,0.3) !important; font-weight: 800 !important;">{{ $employee->name }}</h1>
                    <div class="org-banner-chips">
                        <span class="org-banner-chip" style="background: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                            <i class="fas fa-briefcase" style="color: #a7f3d0 !important; -webkit-text-fill-color: #a7f3d0 !important;"></i>
                            <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 700 !important; background: transparent !important; border: none !important; padding: 0 !important; margin: 0 !important;">{{ $designationName }}</span>
                        </span>
                        <span class="org-banner-chip" style="background: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                            <i class="fas fa-building" style="color: #a7f3d0 !important; -webkit-text-fill-color: #a7f3d0 !important;"></i>
                            <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 700 !important; background: transparent !important; border: none !important; padding: 0 !important; margin: 0 !important;">{{ $deptName }}</span>
                        </span>
                        <span class="org-banner-chip" style="background: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                            <i class="fas fa-id-badge" style="color: #a7f3d0 !important; -webkit-text-fill-color: #a7f3d0 !important;"></i>
                            <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; font-weight: 700 !important; background: transparent !important; border: none !important; padding: 0 !important; margin: 0 !important;">{{ $empCode }}</span>
                        </span>
                        <span class="org-status-pill {{ $status }}" style="padding: 5px 14px; font-size: 0.82rem; font-weight: 800 !important;">
                            <span class="org-status-dot"></span> {{ ucfirst($status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($canManageDirectory)
                    <a href="{{ route('employees.edit', $employee) }}" class="org-btn org-banner-btn-primary" style="background: #ffffff !important; color: #094c32 !important; -webkit-text-fill-color: #094c32 !important; border: 1px solid #ffffff !important; font-weight: 800 !important;">
                        <i class="fas fa-user-pen" style="color: #094c32 !important; -webkit-text-fill-color: #094c32 !important;"></i> Full HR Edit
                    </a>
                    <a href="#directory-editor" class="org-btn org-banner-btn-secondary" style="background: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.4) !important; font-weight: 700 !important;">
                        <i class="fas fa-pen-to-square" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i> Edit Public Info
                    </a>
                @endif
                <a href="{{ route('organization.index') }}" class="org-btn org-banner-btn-outline" style="background: transparent !important; color: #ffffff !important; -webkit-text-fill-color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.45) !important; font-weight: 700 !important;">
                    <i class="fas fa-arrow-left" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i> Back to Directory
                </a>
            </div>
        </div>
    </div>

    <!-- Main Profile Layout Grid -->
    <div class="org-profile-layout">
        <!-- Sidebar Column (Contact & Direct Quick Info) -->
        <div>
            <!-- Contact Card -->
            <div class="org-side-card">
                <div class="org-card-title">
                    <i class="fas fa-address-book me-2"></i> Direct Contact
                </div>
                <div class="d-grid gap-3">
                    <a href="mailto:{{ $employee->email }}" class="org-btn org-btn-secondary w-100 justify-content-start">
                        <i class="fas fa-envelope text-emerald-600 me-2"></i>
                        <span class="text-truncate">{{ $employee->email }}</span>
                    </a>
                    @if($mobile)
                        <a href="tel:{{ preg_replace('/\s+/', '', $mobile) }}" class="org-btn org-btn-secondary w-100 justify-content-start">
                            <i class="fas fa-phone text-blue-600 me-2"></i>
                            <span>{{ $mobile }}</span>
                        </a>
                    @else
                        <div class="p-3 bg-slate-50 border rounded-xl text-slate-500 text-sm font-medium d-flex align-items-center gap-2">
                            <i class="fas fa-phone-slash text-slate-400"></i> Phone not provided
                        </div>
                    @endif

                    @if($employee->slack_id)
                        <div class="p-3 bg-slate-50 border rounded-xl text-slate-700 text-sm font-semibold d-flex align-items-center gap-2">
                            <i class="fab fa-slack text-purple-600"></i> Slack: {{ $employee->slack_id }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Snapshot Card -->
            <div class="org-side-card">
                <div class="org-card-title">
                    <i class="fas fa-user-check me-2"></i> Key Snapshot
                </div>
                <div class="d-grid gap-3">
                    <div class="org-detail-box">
                        <label>Company</label>
                        <div><i class="fas fa-building-flag text-slate-400 me-1"></i> {{ $companyName }}</div>
                    </div>
                    <div class="org-detail-box">
                        <label>Reporting Manager</label>
                        <div><i class="fas fa-user-tie text-emerald-600 me-1"></i> {{ $managerName }}</div>
                    </div>
                    <div class="org-detail-box">
                        <label>Date Joined</label>
                        <div>{{ $joiningDate ? \Carbon\Carbon::parse($joiningDate)->format('d M Y') : 'Not added' }}</div>
                    </div>
                </div>
            </div>

            <!-- Professional & Social Links -->
            <div class="org-side-card">
                <div class="org-card-title">
                    <i class="fas fa-link me-2"></i> Professional Links
                </div>
                @if($profileLinks)
                    <div class="org-social-list">
                        @foreach($profileLinks as $label => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="org-social-pill">
                                <i class="{{ $label === 'LinkedIn' ? 'fab fa-linkedin text-blue-600' : ($label === 'Facebook' ? 'fab fa-facebook text-blue-500' : ($label === 'Instagram' ? 'fab fa-instagram text-pink-600' : ($label === 'X / Twitter' ? 'fab fa-x-twitter text-slate-800' : ($label === 'CV / Resume' ? 'fas fa-file-pdf text-rose-600' : 'fas fa-globe text-emerald-600')))) }}"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-3 bg-slate-50 border rounded-xl text-slate-500 text-sm font-medium">
                        No professional links added yet.
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Column (Detailed Sections) -->
        <div>
            <!-- Organizational & Work Profile Structured Table -->
            <div class="org-main-card">
                <div class="org-card-title">
                    <i class="fas fa-sitemap text-emerald-600 me-2"></i> Organizational & Work Profile
                </div>
                <div class="org-profile-table-wrap">
                    <table class="org-profile-data-table">
                        <tbody>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-id-badge text-emerald-600 me-2"></i> Employee ID</td>
                                <td class="org-data-value"><span class="org-id-badge">{{ $empCode }}</span></td>
                                <td class="org-data-label"><i class="fas fa-building text-sky-600 me-2"></i> Department</td>
                                <td class="org-data-value"><span class="org-dept-tag"><i class="fas fa-building me-1"></i> {{ $deptName }}</span></td>
                            </tr>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-folder-tree text-indigo-600 me-2"></i> Parent Dept</td>
                                <td class="org-data-value">{{ $parentDeptName ?: 'Main Department' }}</td>
                                <td class="org-data-label"><i class="fas fa-user-tie text-emerald-700 me-2"></i> Designation</td>
                                <td class="org-data-value fw-bold text-slate-800">{{ $designationName }}</td>
                            </tr>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-briefcase text-amber-600 me-2"></i> Employment Type</td>
                                <td class="org-data-value">{{ ucfirst($detail?->employment_type ?: 'Full-time') }}</td>
                                <td class="org-data-label"><i class="fas fa-toggle-on text-emerald-600 me-2"></i> Status</td>
                                <td class="org-data-value">
                                    <span class="org-status-pill {{ $status }}">
                                        <span class="org-status-dot"></span> {{ ucfirst($status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-user-check text-blue-600 me-2"></i> Manager</td>
                                <td class="org-data-value fw-bold text-slate-800">{{ $managerName }}</td>
                                <td class="org-data-label"><i class="fas fa-building-flag text-slate-500 me-2"></i> Organization</td>
                                <td class="org-data-value">{{ $companyName }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Personal Context & Tenure Structured Table -->
            <div class="org-main-card">
                <div class="org-card-title">
                    <i class="fas fa-circle-user text-blue-600 me-2"></i> Personal Information & Context
                </div>
                <div class="org-profile-table-wrap">
                    <table class="org-profile-data-table">
                        <tbody>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-cake-candles text-pink-500 me-2"></i> Birthday</td>
                                <td class="org-data-value">{{ $dob ? \Carbon\Carbon::parse($dob)->format('d M (Y)') : 'Not specified' }}</td>
                                <td class="org-data-label"><i class="fas fa-calendar-check text-emerald-600 me-2"></i> Joining Date</td>
                                <td class="org-data-value">{{ $joiningDate ? \Carbon\Carbon::parse($joiningDate)->format('d M Y') : 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-venus-mars text-purple-600 me-2"></i> Gender</td>
                                <td class="org-data-value">{{ ucfirst($detail?->gender ?? $employee->gender ?? 'Not specified') }}</td>
                                <td class="org-data-label"><i class="fas fa-earth-americas text-sky-600 me-2"></i> Country / Location</td>
                                <td class="org-data-value">{{ $detail?->country ?? $employee->country ?? 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td class="org-data-label"><i class="fas fa-language text-amber-600 me-2"></i> Primary Language</td>
                                <td class="org-data-value">{{ $detail?->language ?? $employee->language ?? 'Not specified' }}</td>
                                <td class="org-data-label"><i class="fas fa-clock text-slate-500 me-2"></i> Service Tenure</td>
                                <td class="org-data-value fw-bold text-slate-700">
                                    @if($joiningDate)
                                        {{ \Carbon\Carbon::parse($joiningDate)->diffForHumans(null, true) }}
                                    @else
                                        Not available
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- About & Bio -->
            <div class="org-main-card">
                <div class="org-card-title">
                    <i class="fas fa-quote-left text-emerald-600 me-2"></i> Public About & Bio
                </div>
                <div class="p-3 bg-slate-50 border rounded-xl text-slate-700 font-normal leading-relaxed text-sm">
                    {!! nl2br(e($publicAbout ?: 'No public bio provided for this employee.')) !!}
                </div>
            </div>

            <!-- Skills & Competencies -->
            <div class="org-main-card">
                <div class="org-card-title">
                    <i class="fas fa-award text-amber-600 me-2"></i> Skills & Expertise
                </div>
                @if($detail?->skills)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(array_filter(array_map('trim', explode(',', $detail->skills))) as $skill)
                            <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-pill px-3 py-2 font-semibold text-xs">
                                <i class="fas fa-check-circle me-1 text-emerald-500"></i> {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="p-3 bg-slate-50 border rounded-xl text-slate-500 text-sm font-medium">
                        No skills added yet.
                    </div>
                @endif
            </div>

            <!-- Admin / HR Directory Editor Section -->
            @if($canManageDirectory)
                <div class="org-main-card border-top border-4 border-emerald-500" id="directory-editor" style="padding: 32px !important;">
                    <div class="org-card-title text-emerald-800" style="margin-bottom: 8px !important;">
                        <i class="fas fa-pen-to-square me-2"></i> Admin / HR Directory Editor
                    </div>
                    <p class="text-slate-500 text-sm mb-4" style="margin-left: 2px !important;">
                        Override public Organization Directory details for this employee. Master HR attributes (Salary, Bank, Documents) remain managed in the main HR Employee module.
                    </p>

                    <form method="POST" action="{{ route('organization.directory-profile.update', $employee) }}" enctype="multipart/form-data" style="padding: 6px 4px 0 4px !important;">
                        @csrf
                        @method('PATCH')

                        <div class="org-form-group">
                            <label class="org-form-label">Directory Public Bio</label>
                            <textarea name="directory_about" rows="4" class="org-form-control" style="min-height: 100px; padding: 12px 18px !important;" placeholder="Write a clean professional bio visible across the organization directory.">{{ old('directory_about', $detail?->directory_about) }}</textarea>
                            @error('directory_about')<small class="text-danger font-semibold mt-1">{{ $message }}</small>@enderror
                        </div>

                        <div class="org-form-group">
                            <label class="org-form-label">Skills (Comma Separated)</label>
                            <input type="text" name="skills" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('skills', $detail?->skills) }}" placeholder="e.g. Laravel, React, Operations Management, CRM">
                            @error('skills')<small class="text-danger font-semibold mt-1">{{ $message }}</small>@enderror
                        </div>

                        <div class="row g-3 px-1 mb-2">
                            <div class="col-md-6">
                                <div class="org-form-group mb-0">
                                    <label class="org-form-label">LinkedIn Profile</label>
                                    <input type="url" name="linkedin_url" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('linkedin_url', $detail?->linkedin_url) }}" placeholder="https://linkedin.com/in/username">
                                    @error('linkedin_url')<small class="text-danger font-semibold mt-1">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="org-form-group mb-0">
                                    <label class="org-form-label">Portfolio Website</label>
                                    <input type="url" name="portfolio_url" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('portfolio_url', $detail?->portfolio_url) }}" placeholder="https://portfolio.example.com">
                                    @error('portfolio_url')<small class="text-danger font-semibold mt-1">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 px-1 mb-2">
                            <div class="col-md-4">
                                <div class="org-form-group mb-0">
                                    <label class="org-form-label">Facebook</label>
                                    <input type="url" name="facebook_url" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('facebook_url', $detail?->facebook_url) }}" placeholder="https://facebook.com/username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="org-form-group mb-0">
                                    <label class="org-form-label">Instagram</label>
                                    <input type="url" name="instagram_url" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('instagram_url', $detail?->instagram_url) }}" placeholder="https://instagram.com/username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="org-form-group mb-0">
                                    <label class="org-form-label">X / Twitter</label>
                                    <input type="url" name="x_url" class="org-form-control" style="padding: 12px 18px !important;" value="{{ old('x_url', $detail?->x_url) }}" placeholder="https://x.com/username">
                                </div>
                            </div>
                        </div>

                        <div class="org-form-group mt-3">
                            <label class="org-form-label">Upload CV / Resume (PDF, DOC)</label>
                            <input type="file" name="cv_file" class="org-form-control" style="padding: 10px 18px !important;" accept=".pdf,.doc,.docx,application/pdf">
                            @if($detail?->cv_path)
                                <div class="mt-2 text-xs text-slate-500">
                                    Current CV: <a href="{{ asset($detail->cv_path) }}" target="_blank" class="fw-bold text-emerald-600">View Uploaded CV</a>
                                </div>
                            @endif
                            @error('cv_file')<small class="text-danger font-semibold mt-1">{{ $message }}</small>@enderror
                        </div>

                        <div class="pt-3">
                            <button type="submit" class="org-btn org-btn-primary" style="padding: 12px 28px !important; font-size: 0.92rem !important;">
                                <i class="fas fa-save me-2"></i> Save Directory Profile Details
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

@include('admin.organization-directory.styles')
@endsection
