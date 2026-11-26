@extends('admin.layout.app')

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
<div id="employeeDirectoryPage" class="employee-dashboard employee-directory-modern">

    <div class="emp-breadcrumb-row">
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">
                            <i class="fas fa-house" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Employees</li>
                </ol>
            </nav>
        </div>
    </div>

    @php
        $profileEditor = auth()->user();
        $canShowProfileEditLinks = \App\Support\EmployeeProfileAccess::canShowEditLinks($profileEditor);

        // Calculate employee counts for statistics - FUNCTIONALITY UNCHANGED
        $totalEmployees = $companyEmployeeTotal ?? $employees->count();
        $activeEmployees = $employees->where('employeeDetail.status', 'Active')->count();
        $inactiveEmployees = $employees->where('employeeDetail.status', 'Inactive')->count();
        $noticeEmployees = $employees->filter(function($emp) {
            return $emp->employeeDetail?->employment_status === 'notice';
        })->count();
        $internEmployees = $employees->filter(function($emp) {
            $designation = strtolower($emp->employeeDetail?->designation?->name ?? '');
            return strpos($designation, 'intern') !== false;
        })->count();

        // Filter visible employees - FUNCTIONALITY UNCHANGED
        $visibleEmployees = $employees->filter(function($emp) {
            $detail = $emp->employeeDetail ?? null;
            $status = $detail?->employment_status ?? null;
            $hasNotice = !empty($detail?->notice_end_date);
            $hasProbation = !empty($detail?->probation_end_date);

            if (in_array($status, ['notice','probation'])) return false;
            if ($hasNotice || $hasProbation) return false;
            return true;
        });

        // Reporting flow (top manager -> direct manager) shown under "Reports To"; same links as the Org Tree.
        $reportingLinks = collect($employeeDetails ?? [])->mapWithKeys(fn ($d) => [(int) $d->user_id => ['to' => (int) $d->reporting_to, 'name' => $d->user?->name]]);
        $reportingFlow = function ($employee) use ($reportingLinks) {
            $chain = [];
            $seen = [(int) $employee->id => true];
            $managerId = (int) ($employee->employeeDetail?->reporting_to ?? 0);
            while ($managerId && ! isset($seen[$managerId]) && count($chain) < 8) {
                $seen[$managerId] = true;
                $name = $reportingLinks[$managerId]['name'] ?? null;
                if (! $name) break;
                array_unshift($chain, $name);
                $managerId = $reportingLinks[$managerId]['to'] ?? 0;
            }
            return $chain;
        };

        $emptyCompanyName = ($selectedCompanyId ? $companies->firstWhere('id', $selectedCompanyId)?->name : null)
            ?: (session('current_company_name') ?: (auth()->user()?->company?->name ?? config('app.name')));
    @endphp

    <header class="directory-hero">
        <div class="directory-hero-intro">
            <div class="directory-hero-icon" aria-hidden="true">
                <i class="fas fa-address-book"></i>
            </div>
            <div>
                <h1>Employee Management</h1>
                <p>Manage employee records and permissions.</p>
            </div>
        </div>
        <div class="directory-hero-actions">
            <div class="dropdown">
                <button class="btn directory-button dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                    <i class="fas fa-bolt" aria-hidden="true"></i>Quick Actions
                </button>
                <ul class="dropdown-menu directory-quick-menu">
                    <li><a class="dropdown-item" href="{{ route('employees.archive') }}"><i class="fas fa-box-archive me-2" aria-hidden="true"></i>Archived Employees</a></li>
                    <li><a class="dropdown-item" href="#" id="bulkDeleteTrigger"><i class="fas fa-user-slash me-2 text-danger" aria-hidden="true"></i>Archive Inactive</a></li>
                </ul>
            </div>
            <a href="{{ route('employees.archive') }}" class="btn directory-button">
                <i class="fas fa-box-archive" aria-hidden="true"></i>Archived
            </a>
            <a href="{{ route('employees.create') }}" class="btn directory-button directory-button-primary">
                <i class="fas fa-user-plus" aria-hidden="true"></i>Add Employee
            </a>
            <button type="button" class="btn directory-button" data-bs-toggle="modal" data-bs-target="#inviteModal">
                <i class="fas fa-envelope" aria-hidden="true"></i>Invite
            </button>
        </div>
    </header>

    <dl class="directory-metrics" aria-label="Employee totals">
        <div class="directory-metric">
            <dt>Total Employees</dt>
            <dd>{{ $totalEmployees }} <span class="directory-metric-icon" aria-hidden="true"><i class="fas fa-users"></i></span></dd>
        </div>
        <div class="directory-metric directory-metric-active">
            <dt>Active</dt>
            <dd>{{ $activeEmployees }} <span class="directory-metric-icon" aria-hidden="true"><i class="fas fa-user-check"></i></span></dd>
        </div>
        <div class="directory-metric directory-metric-inactive">
            <dt>Inactive</dt>
            <dd>{{ $inactiveEmployees }} <span class="directory-metric-icon" aria-hidden="true"><i class="fas fa-user-slash"></i></span></dd>
        </div>
        <div class="directory-metric directory-metric-notice">
            <dt>On Notice</dt>
            <dd>{{ $noticeEmployees }} <span class="directory-metric-icon" aria-hidden="true"><i class="fas fa-clock"></i></span></dd>
        </div>
    </dl>

    @if(\App\Services\TenantScope::isPlatformAdmin())
    {{-- Company Selector - SaaS company workspace filter (platform Super Admin only; company users see only their own company) --}}
    <div class="company-switcher-grid !grid !grid-cols-1 !gap-3 sm:!grid-cols-2 lg:!grid-cols-3 2xl:!grid-cols-6 !mb-5" style="position: relative !important; z-index: 1 !important;">
        <a href="{{ route('employees.index', request()->except(['company_id', 'page'])) }}"
           class="company-switch-card {{ empty($selectedCompanyId) ? 'active' : '' }}">
            <span class="company-switch-icon" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                <i class="fas fa-layer-group" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i>
            </span>
            <span class="company-switch-meta">
                <strong>All Companies</strong>
                <small>{{ $companyStats->sum('employees') }} employees</small>
            </span>
        </a>
        @foreach($companyStats as $stat)
            @php
                $company = $stat['company'];
                $companyQuery = array_merge(request()->except('page'), ['company_id' => $company->id]);
            @endphp
            <a href="{{ route('employees.index', $companyQuery) }}"
               class="company-switch-card {{ (string) $selectedCompanyId === (string) $company->id ? 'active' : '' }}">
                <span class="company-switch-icon" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                    @if($company->logo)
                        <img src="{{ asset($company->logo) }}" alt="{{ $company->name }}">
                    @else
                        <span style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">{{ strtoupper(substr($company->short_name ?: $company->company_code ?: $company->name, 0, 2)) }}</span>
                    @endif
                </span>
                <span class="company-switch-meta">
                    <strong>{{ $company->name }}</strong>
                    <small>{{ $stat['employees'] }} employees • {{ $stat['active'] }} active</small>
                </span>
            </a>
        @endforeach
    </div>
    @if($selectedCompanyId)
        <div class="company-module-rail !mb-5">
            <span>Company workspace</span>
            <a href="{{ route('attendance.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="{{ route('leaves.index', ['company_id' => $selectedCompanyId]) }}"><i class="fas fa-calendar-days"></i> Leaves</a>
        </div>
    @endif
    @endif

    {{-- Invite Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane me-2"></i>
                        Invite to {{ session('current_company_name') ?: (auth()->user()?->company?->name ?? config('app.name')) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs" id="inviteTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="invite-email-tab" data-bs-toggle="tab" data-bs-target="#invite-email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Invite by Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invite-link-tab" data-bs-toggle="tab" data-bs-target="#invite-link" type="button" role="tab">
                                <i class="fas fa-link me-2"></i>Invite by Link
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="inviteTabsContent">
                        <!-- Invite by Email -->
                        <div class="tab-pane fade show active" id="invite-email" role="tabpanel">
                            <div class="alert alert-info bg-light">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Employees will receive an email to log in and update their profile.
                            </div>

                            <form id="inviteEmailForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="e.g. name@example.com" autocomplete="email" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Personal Message <span class="text-muted">(Optional)</span></label>
                                    <textarea name="message" id="inviteMessage" class="form-control" rows="3" placeholder="Add a welcome message..."></textarea>
                                </div>
                            </form>
                        </div>

                        <!-- Invite by Link -->
                        <div class="tab-pane fade" id="invite-link" role="tabpanel">
                            <div class="alert alert-light border">
                                <i class="fas fa-link text-primary me-2"></i>
                                Create a signed link for one employee. It expires in 7 days and can be used once. Email delivery is not required.
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold" for="inviteLinkEmail">Employee Email Address *</label>
                                <input type="email" class="form-control" id="inviteLinkEmail" placeholder="name@example.com" autocomplete="email" required>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary px-4" id="createLinkBtn">
                                    <i class="fas fa-plus-circle me-2"></i>Generate Invite Link
                                </button>
                            </div>

                            <div class="mt-4" id="linkContainer" style="display:none;">
                                <div class="alert alert-success border-0 bg-success bg-opacity-10">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span class="fw-medium">Invitation link created successfully!</span>
                                    <small class="d-block mt-1">This link will expire in 7 days</small>
                                </div>

                                <div class="input-group">
                                    <input type="text" class="form-control border-end-0" id="inviteLink" readonly>
                                    <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">
                                        <i class="fas fa-copy" aria-hidden="true"></i><span class="visually-hidden">Copy invitation link</span>
                                    </button>
                                    <button class="btn btn-outline-success" type="button" id="shareLinkBtn" title="Share via Email">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="inviteEmailForm" id="sendInviteBtn" class="btn btn-primary">
                        <span id="sendInviteSpinner" class="spinner-border spinner-border-sm me-2 d-none"></span>
                        <i class="fas fa-paper-plane me-2"></i>Send Invitation
                    </button>
                </div>
                <div class="mt-3 px-3" id="inviteEmailAlert" style="display:none;"></div>
            </div>
        </div>
    </div>

    {{-- Blocked Delete Modal - FUNCTIONALITY UNCHANGED --}}
    <div class="modal fade" id="blockedDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Restricted
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-users-slash fa-4x text-warning mb-3"></i>
                    <h5 id="blockedEmployeeName" class="fw-bold mb-2"></h5>
                    <p class="text-muted mb-0" id="blockedEmployeeReason"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a href="#" id="blocked-view-subordinates" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>View Team Members
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Employee: locked (needs inactive / no team) or typed confirmation for a permanent delete --}}
    <div class="modal fade emp-lifecycle-modal" id="employeeDeleteModal" tabindex="-1" aria-labelledby="employeeDeleteTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="elm-head">
                    <span class="elm-icon" id="employeeDeleteIcon" aria-hidden="true"><i class="fas fa-trash-can"></i></span>
                    <div class="elm-head-text">
                        <h5 class="modal-title" id="employeeDeleteTitle">Delete employee</h5>
                        <p id="employeeDeleteSubtitle">This permanently removes the employee.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="elm-body">
                    <div class="elm-person">
                        <span class="elm-avatar" id="employeeDeleteInitials" aria-hidden="true">E</span>
                        <div>
                            <strong id="employeeDeleteName">Employee</strong>
                            <small id="employeeDeleteMeta"></small>
                        </div>
                        <span class="elm-pill" id="employeeDeleteStatus">Inactive</span>
                    </div>

                    <ol class="elm-steps" aria-label="Delete requirements">
                        <li id="employeeDeleteStepInactive"><span class="elm-step-mark" aria-hidden="true"></span><span>Employee is marked <strong>Inactive</strong></span></li>
                        <li id="employeeDeleteStepTeam"><span class="elm-step-mark" aria-hidden="true"></span><span>No team members report to this employee</span></li>
                    </ol>

                    <div class="elm-locked" id="employeeDeleteLocked">
                        <p id="employeeDeleteLockedText"></p>
                    </div>

                    <form id="employeeDeleteForm" method="POST" action="#" class="elm-confirm">
                        @csrf
                        @method('DELETE')
                        <div class="elm-warning">
                            <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                            <div>
                                <strong>This cannot be undone.</strong>
                                The employee's login, profile and their personal records (attendance, leave and similar history) are removed for good.
                                To keep history instead, use <em>Archive Employee</em> — it can be restored later.
                            </div>
                        </div>
                        <label for="employeeDeleteConfirm" class="elm-label">Type <code id="employeeDeleteCode"></code> to confirm</label>
                        <input type="text" id="employeeDeleteConfirm" name="confirm_code" class="elm-input" autocomplete="off" spellcheck="false" required>
                    </form>
                </div>
                <div class="elm-foot">
                    <button type="button" class="elm-btn is-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="elm-btn is-warning" id="employeeDeleteMakeInactive"><i class="fas fa-user-clock" aria-hidden="true"></i> Mark Inactive</button>
                    <button type="submit" form="employeeDeleteForm" class="elm-btn is-danger" id="employeeDeleteSubmit" disabled><i class="fas fa-trash-can" aria-hidden="true"></i> Delete permanently</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Mark Inactive: step 1 before an employee can be deleted --}}
    <div class="modal fade emp-lifecycle-modal" id="employeeInactiveModal" tabindex="-1" aria-labelledby="employeeInactiveTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="employeeInactiveForm" method="POST" action="#">
                @csrf
                <div class="elm-head">
                    <span class="elm-icon is-warning" aria-hidden="true"><i class="fas fa-user-clock"></i></span>
                    <div class="elm-head-text">
                        <h5 class="modal-title" id="employeeInactiveTitle">Mark employee inactive</h5>
                        <p>Inactive employees can then be archived or deleted.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="elm-body">
                    <div class="elm-person">
                        <span class="elm-avatar" id="employeeInactiveInitials" aria-hidden="true">E</span>
                        <div>
                            <strong id="employeeInactiveName">Employee</strong>
                            <small id="employeeInactiveMeta"></small>
                        </div>
                        <span class="elm-pill is-active">Active</span>
                    </div>
                    <label for="employeeInactiveDate" class="elm-label">Exit date</label>
                    <input type="date" id="employeeInactiveDate" name="exit_date" class="elm-input" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}" required>
                    <p class="elm-hint">The employee's status changes to Inactive with this exit date. You can reactivate them later by editing their profile.</p>
                </div>
                <div class="elm-foot">
                    <button type="button" class="elm-btn is-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="elm-btn is-warning" id="employeeInactiveSubmit"><i class="fas fa-user-clock" aria-hidden="true"></i> Mark Inactive</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .emp-lifecycle-modal {
            --elm-surface: #ffffff; --elm-soft: #f3f5fc; --elm-ink: #10142c; --elm-muted: #545d82; --elm-border: rgba(16, 20, 44, .1);
            --elm-danger: #dc2626; --elm-danger-soft: #fef2f2; --elm-danger-ink: #991b1b; --elm-warn: #d97706; --elm-warn-soft: #fffbeb;
            --elm-ok: #059669; --elm-ok-soft: #ecfdf5; --elm-primary: #2f6bff;
        }
        :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark, body.dark-mode) .emp-lifecycle-modal {
            --elm-surface: #0f1530; --elm-soft: #141b3d; --elm-ink: #eef1fb; --elm-muted: #9aa3c7; --elm-border: rgba(238, 241, 251, .12);
            --elm-danger: #f87171; --elm-danger-soft: rgba(239, 68, 68, .12); --elm-danger-ink: #fecaca; --elm-warn: #fbbf24; --elm-warn-soft: rgba(245, 158, 11, .12);
            --elm-ok: #34d399; --elm-ok-soft: rgba(16, 185, 129, .12);
        }
        .emp-lifecycle-modal .modal-dialog { max-width: 500px; }
        .emp-lifecycle-modal .modal-content { border: 1px solid var(--elm-border) !important; border-radius: 20px !important; background: var(--elm-surface) !important; color: var(--elm-ink) !important; box-shadow: 0 24px 60px rgba(15, 23, 42, .25) !important; overflow: hidden; }
        .emp-lifecycle-modal .elm-head { display: flex; align-items: flex-start; gap: 14px; padding: 20px 22px 12px; }
        .emp-lifecycle-modal .elm-icon { width: 46px; height: 46px; flex-shrink: 0; border-radius: 14px; display: grid; place-items: center; background: var(--elm-danger-soft); }
        .emp-lifecycle-modal .elm-icon i { font-size: 19px; color: var(--elm-danger) !important; -webkit-text-fill-color: var(--elm-danger) !important; }
        .emp-lifecycle-modal .elm-icon.is-locked, .emp-lifecycle-modal .elm-icon.is-warning { background: var(--elm-warn-soft); }
        .emp-lifecycle-modal .elm-icon.is-locked i, .emp-lifecycle-modal .elm-icon.is-warning i { color: var(--elm-warn) !important; -webkit-text-fill-color: var(--elm-warn) !important; }
        .emp-lifecycle-modal .elm-head-text { flex: 1; min-width: 0; }
        .emp-lifecycle-modal .modal-title { margin: 2px 0 0; font-size: 1.08rem; font-weight: 700; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; }
        .emp-lifecycle-modal .elm-head-text p { margin: 3px 0 0; font-size: .84rem; color: var(--elm-muted) !important; -webkit-text-fill-color: var(--elm-muted) !important; }
        :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark, body.dark-mode) .emp-lifecycle-modal .btn-close { filter: invert(1) grayscale(1) brightness(1.6); }
        .emp-lifecycle-modal .elm-body { padding: 6px 22px 8px; }
        .emp-lifecycle-modal .elm-person { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--elm-border); border-radius: 14px; background: var(--elm-soft); }
        .emp-lifecycle-modal .elm-avatar { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #2f6bff, #8b5cf6); color: #fff !important; -webkit-text-fill-color: #fff !important; font-weight: 700; font-size: .9rem; }
        .emp-lifecycle-modal .elm-person > div { flex: 1; min-width: 0; }
        .emp-lifecycle-modal .elm-person strong { display: block; font-size: .95rem; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .emp-lifecycle-modal .elm-person small { display: block; font-size: .78rem; color: var(--elm-muted) !important; -webkit-text-fill-color: var(--elm-muted) !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .emp-lifecycle-modal .elm-pill { flex-shrink: 0; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; background: var(--elm-danger-soft); color: var(--elm-danger) !important; -webkit-text-fill-color: var(--elm-danger) !important; }
        .emp-lifecycle-modal .elm-pill.is-active { background: var(--elm-ok-soft); color: var(--elm-ok) !important; -webkit-text-fill-color: var(--elm-ok) !important; }
        .emp-lifecycle-modal .elm-steps { list-style: none; margin: 14px 0 0; padding: 0; display: grid; gap: 8px; }
        .emp-lifecycle-modal .elm-steps li { display: flex; align-items: center; gap: 10px; font-size: .86rem; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; }
        .emp-lifecycle-modal .elm-steps li span, .emp-lifecycle-modal .elm-steps li strong { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
        .emp-lifecycle-modal .elm-step-mark { width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; border: 2px solid var(--elm-border); font-size: 11px; font-weight: 800; }
        .emp-lifecycle-modal .elm-steps li.is-done .elm-step-mark { border-color: var(--elm-ok); background: var(--elm-ok); }
        .emp-lifecycle-modal .elm-steps li.is-done .elm-step-mark::after { content: '✓'; color: #fff; -webkit-text-fill-color: #fff; }
        .emp-lifecycle-modal .elm-steps li.is-todo .elm-step-mark { border-color: var(--elm-warn); }
        .emp-lifecycle-modal .elm-steps li.is-todo .elm-step-mark::after { content: '!'; color: var(--elm-warn); -webkit-text-fill-color: var(--elm-warn); }
        .emp-lifecycle-modal .elm-steps li.is-todo { color: var(--elm-warn) !important; -webkit-text-fill-color: var(--elm-warn) !important; font-weight: 600; }
        .emp-lifecycle-modal .elm-locked p { margin: 14px 0 0; padding: 12px 14px; border-radius: 12px; background: var(--elm-warn-soft); font-size: .86rem; line-height: 1.55; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; }
        .emp-lifecycle-modal .elm-warning { display: flex; gap: 10px; margin: 14px 0 0; padding: 12px 14px; border-radius: 12px; background: var(--elm-danger-soft); font-size: .84rem; line-height: 1.55; color: var(--elm-danger-ink) !important; -webkit-text-fill-color: var(--elm-danger-ink) !important; }
        .emp-lifecycle-modal .elm-warning :is(strong, em, div) { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
        .emp-lifecycle-modal .elm-warning strong { display: block; margin-bottom: 2px; }
        .emp-lifecycle-modal .elm-warning i { margin-top: 3px; color: var(--elm-danger) !important; -webkit-text-fill-color: var(--elm-danger) !important; }
        .emp-lifecycle-modal .elm-label { display: block; margin: 16px 0 6px; font-size: .84rem; font-weight: 600; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; }
        .emp-lifecycle-modal .elm-label code { padding: 1px 7px; border-radius: 6px; background: var(--elm-soft); border: 1px solid var(--elm-border); font-size: .82rem; color: var(--elm-danger) !important; -webkit-text-fill-color: var(--elm-danger) !important; }
        .emp-lifecycle-modal .elm-input { display: block; width: 100%; height: 44px; padding: 0 14px !important; border: 1px solid var(--elm-border) !important; border-radius: 12px !important; background: var(--elm-surface) !important; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; font: inherit; outline: none; box-shadow: none !important; color-scheme: light dark; }
        .emp-lifecycle-modal .elm-input:focus { border-color: var(--elm-primary) !important; box-shadow: 0 0 0 4px rgba(47, 107, 255, .18) !important; }
        .emp-lifecycle-modal .elm-input.is-match { border-color: var(--elm-danger) !important; }
        .emp-lifecycle-modal .elm-input::placeholder { color: var(--elm-muted) !important; -webkit-text-fill-color: var(--elm-muted) !important; opacity: .6; }
        .emp-lifecycle-modal .elm-hint { margin: 8px 0 0; font-size: .8rem; line-height: 1.5; color: var(--elm-muted) !important; -webkit-text-fill-color: var(--elm-muted) !important; }
        .emp-lifecycle-modal .elm-foot { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; padding: 16px 22px 20px; }
        .emp-lifecycle-modal .elm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; height: 42px; padding: 0 18px !important; border-radius: 12px !important; border: 1px solid transparent !important; font: inherit; font-size: .88rem; font-weight: 600; cursor: pointer; width: auto !important; box-shadow: none !important; transition: filter .15s, opacity .15s; }
        .emp-lifecycle-modal .elm-btn[hidden] { display: none !important; }
        .emp-lifecycle-modal .elm-btn i { color: inherit !important; -webkit-text-fill-color: currentColor !important; }
        .emp-lifecycle-modal .elm-btn.is-ghost { background: transparent !important; border-color: var(--elm-border) !important; color: var(--elm-ink) !important; -webkit-text-fill-color: var(--elm-ink) !important; }
        .emp-lifecycle-modal .elm-btn.is-danger { background: #dc2626 !important; color: #fff !important; -webkit-text-fill-color: #fff !important; }
        .emp-lifecycle-modal .elm-btn.is-warning { background: #d97706 !important; color: #fff !important; -webkit-text-fill-color: #fff !important; }
        .emp-lifecycle-modal .elm-btn:hover:not(:disabled) { filter: brightness(1.08); }
        .emp-lifecycle-modal .elm-btn:disabled { opacity: .45; cursor: not-allowed; }
        .emp-lifecycle-modal .elm-btn:focus-visible { outline: 3px solid rgba(47, 107, 255, .4); outline-offset: 2px; }
        .employee-dashboard .dropdown-menu .employee-delete-trigger.is-locked,
        .employee-dashboard .dropdown-menu .employee-delete-trigger.is-locked i { color: #94a3b8 !important; -webkit-text-fill-color: #94a3b8 !important; }
        .employee-dashboard .dropdown-menu .employee-delete-trigger.text-danger i { color: #dc2626 !important; -webkit-text-fill-color: #dc2626 !important; }
        @media (max-width: 575.98px) {
            .emp-lifecycle-modal .elm-foot { flex-direction: column-reverse; }
            .emp-lifecycle-modal .elm-btn { width: 100% !important; }
        }
    </style>

    {{-- Alerts Section - FUNCTIONALITY UNCHANGED --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 animate-shake" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 animate-slideDown" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @include('admin.employees.partials.org-tree')

    <div class="filter-export-card">
        <div class="directory-panel-header">
            <div class="directory-panel-title">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-sliders"></i></span>
                <h2>Filter Employees</h2>
            </div>
            <div class="directory-export">
                <span id="export-selected-count" class="directory-count">0 selected</span>
                <button type="button" class="btn directory-button" id="export-all">
                    <i class="fas fa-download" aria-hidden="true"></i>Export All
                </button>
                <div class="directory-export-formats" role="group" aria-label="Export selected employees">
                    <button type="button" class="btn directory-button" id="export-copy" disabled title="Copy" aria-label="Copy selected employees">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn directory-button" id="export-csv" disabled title="CSV" aria-label="Export selected employees as CSV">
                        <i class="fas fa-file-csv" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn directory-button" id="export-excel" disabled title="Excel" aria-label="Export selected employees as Excel">
                        <i class="fas fa-file-excel" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn directory-button" id="export-pdf" disabled title="PDF" aria-label="Export selected employees as PDF">
                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn directory-button" id="export-print" disabled title="Print" aria-label="Print selected employees">
                        <i class="fas fa-print" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="directory-filter-body">
            <form method="GET" action="{{ route('employees.index') }}" class="filter-form">
                <div class="directory-filter-fields">
                    @if($selectedCompanyId)
                        <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                    @endif
                    <!-- Employee ID -->
                    <div class="directory-filter-field">
                        <label for="directory-employee-id">
                            <i class="fas fa-id-card" aria-hidden="true"></i>Employee ID
                        </label>
                        <select name="employee_id" id="directory-employee-id" class="form-select select2">
                            <option value="">All Employees</option>
                            @php
                                $edOptions = $employeeDetails->filter(function($d){
                                    $status = $d->employment_status ?? null;
                                    $hasNotice = !empty($d->notice_end_date);
                                    $hasProb = !empty($d->probation_end_date);
                                    if (in_array($status, ['notice','probation'])) return false;
                                    if ($hasNotice || $hasProb) return false;
                                    return true;
                                });
                            @endphp
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->employee_id }}" {{ request('employee_id') == $detail->employee_id ? 'selected' : '' }}>
                                    {{ $detail->employee_id }} - {{ $detail->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Designation -->
                    <div class="directory-filter-field">
                        <label for="directory-designation">
                            <i class="fas fa-briefcase" aria-hidden="true"></i>Designation
                        </label>
                        <select name="designation_id" id="directory-designation" class="form-select select2">
                            <option value="">All Designations</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                                    {{ $designation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Name/Email -->
                    <div class="directory-filter-field">
                        <label for="directory-user">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>Name or email
                        </label>
                        <select name="user_id" id="directory-user" class="form-select select2">
                            <option value="">Search Employee...</option>
                            @foreach($edOptions as $detail)
                                <option value="{{ $detail->user_id }}" {{ request('user_id') == $detail->user_id ? 'selected' : '' }}>
                                    {{ $detail->user->name ?? 'N/A' }} - {{ $detail->user->email ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="directory-filter-actions">
                        <button type="submit" class="btn directory-button directory-button-primary">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>Apply Filters
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn directory-button">
                            <i class="fas fa-rotate-right" aria-hidden="true"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-card">
        <div class="directory-panel-header">
            <div class="directory-panel-title">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-list-ul"></i></span>
                <h2>Employee List</h2>
                <span class="directory-count">{{ $visibleEmployees->count() }} employees</span>
            </div>
            <button id="btn-bulk-delete" class="btn directory-button btn-bulk-delete is-awaiting-selection" aria-disabled="true" title="Select inactive employees to archive">
                <i class="fas fa-box-archive" aria-hidden="true"></i>Archive Inactive
            </button>
        </div>

        @if($visibleEmployees->isEmpty())
            <div class="empty-state">
                <span class="directory-panel-icon" aria-hidden="true"><i class="fas fa-users"></i></span>
                <h3>No employees found</h3>
                <p>Adjust your filters, add an employee to {{ $emptyCompanyName }}, or send invitations.</p>
                <div class="empty-state-actions">
                    <a href="{{ route('employees.create') }}" class="btn directory-button directory-button-primary">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>Add First Employee
                    </a>
                    <button type="button" class="btn directory-button" data-bs-toggle="modal" data-bs-target="#inviteModal">
                        <i class="fas fa-envelope" aria-hidden="true"></i>Send Invitations
                    </button>
                </div>
            </div>
        @else
        <div class="table-responsive !w-full !overflow-x-auto">
            <table id="employeeTable" data-pms-export="off" class="table table-hover align-middle mb-0 employee-list-table">
                <thead>
                    <tr>
                        <th scope="col" class="no-export" data-pms-selection-column aria-label="Select rows">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th scope="col">Emp ID</th>
                        <th scope="col">Employee Name</th>
                        <th scope="col">Company</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role & Reporting</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                        @foreach($visibleEmployees as $employee)
                        <tr id="employee-row-{{ $employee->id }}"
                            class="@if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) table-warning @endif">
                            <td class="no-export" data-pms-selection-column>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input employee-checkbox"
                                           value="{{ $employee->id }}"
                                           @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0) disabled @endif
                                           data-employee-id="{{ $employee->employeeDetail?->employee_id ?? '-' }}"
                                           data-name="{{ htmlspecialchars($employee->name, ENT_QUOTES, 'UTF-8') }}"
                                           data-email="{{ $employee->email ?? '-' }}"
                                           data-company="{{ htmlspecialchars($employee->company?->name ?? 'Unassigned', ENT_QUOTES, 'UTF-8') }}"
                                           data-designation="{{ htmlspecialchars($employee->employeeDetail?->designation?->name ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                           data-reporting-to="{{ htmlspecialchars($employee->employeeDetail?->reportingTo?->name ?? 'N/A', ENT_QUOTES, 'UTF-8') }}"
                                           data-status="{{ $employee->employeeDetail?->status === 'Active' ? 'Active' : ($employee->employeeDetail?->status === 'Inactive' ? 'Inactive' : 'N/A') }}">
                                    </div>
                            </td>
                            <td>
                                <span class="employee-id-badge">
                                    {{ $employee->employeeDetail?->employee_id ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="employee-info">
                                    @if(!empty($employee->profile_image))
                                        <img src="{{ asset($employee->profile_image) }}" alt="Profile" class="profile-image">
                                    @else
                                        <div class="profile-placeholder">
                                            <span class="profile-initials">{{ strtoupper(substr($employee->name ?? 'E', 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div class="employee-details">
                                        <h6 class="employee-name">{{ $employee->name }}</h6>
                                        <span class="employee-designation" style="color: var(--emp-designation-color, #64748b) !important; -webkit-text-fill-color: var(--emp-designation-color, #64748b) !important;">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                        <div class="employee-badges">
                                            @php
                                                $detail = $employee->employeeDetail;
                                                $status = $detail?->employment_status ?? null;
                                                $designationName = strtolower($detail?->designation?->name ?? '');
                                            @endphp

                                            @if ($status === 'notice')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i> Notice
                                                </span>
                                            @elseif (strpos($designationName, 'intern') !== false)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-graduation-cap me-1"></i> Intern
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-user-tie me-1"></i> Employee
                                                </span>
                                            @endif

                                            @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                                <span class="badge bg-warning text-dark ms-1">
                                                    <i class="fas fa-users me-1"></i> Lead
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="employee-company-chip">
                                    <span class="employee-company-logo" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;">
                                        @if($employee->company?->logo)
                                             <img src="{{ asset($employee->company->logo) }}" alt="{{ $employee->company->name }}">
                                        @else
                                            {{ strtoupper(substr($employee->company?->short_name ?: $employee->company?->company_code ?: $employee->company?->name ?: 'NA', 0, 2)) }}
                                        @endif
                                    </span>
                                    <span class="employee-company-name">{{ $employee->company?->name ?? 'Unassigned' }}</span>
                                </span>
                            </td>
                            <td>
                                <div class="email-wrapper">
                                    <i class="fas fa-envelope email-icon"></i>
                                    <a href="mailto:{{ $employee->email }}" class="email-link">
                                        {{ Str::limit($employee->email, 25) }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="role-info">
                                    <span class="role-name" style="color: var(--emp-role-color, #1f2937) !important; -webkit-text-fill-color: var(--emp-role-color, #1f2937) !important;">{{ $employee->employeeDetail?->designation?->name ?? '-' }}</span>
                                    <span class="reports-to" style="color: var(--emp-reports-color, #64748b) !important; -webkit-text-fill-color: var(--emp-reports-color, #64748b) !important;">
                                        <i class="fa-solid fa-users fas" style="color: #22D3EE !important; -webkit-text-fill-color: #22D3EE !important; font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free' !important; font-weight: 900 !important; font-style: normal !important; display: inline-block !important;"></i>
                                        {{ $employee->employeeDetail?->reportingTo?->name ?? 'N/A' }}
                                    </span>
                                    @php $flow = $reportingFlow($employee); @endphp
                                    @if(count($flow) > 1)
                                        <span class="reports-flow" title="Reporting flow: {{ implode(' › ', $flow) }} › {{ $employee->name }}">
                                            <i class="fas fa-sitemap" aria-hidden="true"></i>
                                            <span>{{ implode(' › ', $flow) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $rowStatus = $employee->employeeDetail?->status;
                                    $isStatusActive = $rowStatus === 'Active';
                                    $isStatusInactive = $rowStatus === 'Inactive';
                                    $nextStatus = $isStatusActive ? 'Inactive' : 'Active';
                                @endphp
                                <button
                                    type="button"
                                    class="status-badge {{ $isStatusActive ? 'status-active' : ($isStatusInactive ? 'status-inactive' : 'status-unknown') }} js-employee-status-toggle"
                                    data-employee-id="{{ $employee->id }}"
                                    data-status="{{ $isStatusActive ? 'Active' : ($isStatusInactive ? 'Inactive' : 'N/A') }}"
                                    title="Click to switch status to {{ $nextStatus }}"
                                    aria-label="Switch {{ $employee->name }} status to {{ $nextStatus }}"
                                >
                                    <span class="status-dot"></span>
                                    <span class="status-text">{{ $isStatusActive ? 'Active' : ($isStatusInactive ? 'Inactive' : 'N/A') }}</span>
                                </button>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn-action-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Details
                                            </a>
                                        </li>
                                        @if($canShowProfileEditLinks && \App\Support\EmployeeProfileAccess::canEditTarget($profileEditor, $employee))
                                            <li>
                                                <a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}">
                                                    <i class="fas fa-edit text-info me-2"></i> Edit Profile
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <button type="button" class="dropdown-item text-warning" onclick="openResetPasswordModal({{ $employee->id }}, '{{ addslashes($employee->name) }}')">
                                                <i class="fas fa-key me-2"></i> Reset Password
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if(isset($employee->subordinate_count) && $employee->subordinate_count > 0)
                                        <li>
                                            <button class="dropdown-item text-warning blocked-delete-btn" type="button"
                                                data-employee-name="{{ $employee->name }}"
                                                data-subordinate-count="{{ $employee->subordinate_count }}"
                                                data-employee-id="{{ $employee->id }}">
                                                <i class="fas fa-ban me-2"></i> Archive Restricted
                                            </button>
                                        </li>
                                        @else
                                        <li class="js-employee-archive-item" data-destroy-url="{{ route('employees.destroy', $employee->id) }}">
                                            @if($employee->employeeDetail?->status !== 'Inactive')
                                                <button class="dropdown-item text-muted" type="button" disabled>
                                                    <i class="fas fa-lock me-2"></i> Archive only inactive
                                                </button>
                                            @else
                                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                      onsubmit="return confirm('Archive this inactive employee? The data will move to Archived Employees and can be restored.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">
                                                        <i class="fas fa-box-archive me-2"></i> Archive Employee
                                                    </button>
                                                </form>
                                            @endif
                                        </li>
                                        @endif
                                        @php
                                            $employeeRole = strtolower(trim((string) ($employee->role ?? '')));
                                            $isInactiveEmployee = strcasecmp((string) ($employee->employeeDetail?->status ?? ''), 'Inactive') === 0;
                                            $deleteState = match (true) {
                                                $employeeRole !== 'employee' => 'privileged',
                                                isset($employee->subordinate_count) && $employee->subordinate_count > 0 => 'has-team',
                                                ! $isInactiveEmployee => 'active',
                                                default => 'ready',
                                            };
                                        @endphp
                                        @if(! $isInactiveEmployee && $employee->employeeDetail && $employeeRole === 'employee')
                                            <li>
                                                <button type="button" class="dropdown-item text-warning employee-inactive-trigger"
                                                        data-employee-name="{{ $employee->name }}"
                                                        data-employee-code="{{ $employee->employeeDetail?->employee_id }}"
                                                        data-inactive-url="{{ route('employees.inactive.update', $employee->id) }}">
                                                    <i class="fas fa-user-clock me-2"></i> Mark Inactive
                                                </button>
                                            </li>
                                        @endif
                                        <li>
                                            <button type="button" class="dropdown-item employee-delete-trigger {{ $deleteState === 'ready' ? 'text-danger' : 'is-locked' }}"
                                                    data-delete-state="{{ $deleteState }}"
                                                    data-employee-name="{{ $employee->name }}"
                                                    data-employee-code="{{ $employee->employeeDetail?->employee_id }}"
                                                    data-employee-role="{{ $employee->employeeDetail?->designation?->name ?? 'No designation' }}"
                                                    data-employee-department="{{ $employee->employeeDetail?->department?->name ?? 'No department' }}"
                                                    data-employee-team="{{ (int) ($employee->subordinate_count ?? 0) }}"
                                                    data-delete-url="{{ route('employees.delete-permanent', $employee->id) }}"
                                                    data-inactive-url="{{ route('employees.inactive.update', $employee->id) }}">
                                                <i class="fas {{ $deleteState === 'ready' ? 'fa-trash-can' : 'fa-lock' }} me-2"></i> Delete Employee
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination & Show Entries --}}
        <div class="table-footer !flex !flex-col !gap-4 !p-4 sm:!p-5 lg:!flex-row lg:!items-center lg:!justify-between">
            <div class="footer-left !flex !w-full !justify-center lg:!w-auto lg:!justify-start">
                <div class="show-entries !flex !flex-wrap !items-center !justify-center !gap-2">
                    <span style="color: var(--emp-footer-text, #4b5563) !important; -webkit-text-fill-color: var(--emp-footer-text, #4b5563) !important; font-weight: 600;">Show</span>
                    <select class="form-select form-select-sm" id="showEntries" style="background-color: var(--emp-footer-select-bg, #ffffff) !important; color: var(--emp-footer-select-color, #1f2937) !important; -webkit-text-fill-color: var(--emp-footer-select-color, #1f2937) !important; border-color: var(--emp-footer-select-border, #e0d7ff) !important;">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ request('per_page', 30) == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="color: var(--emp-footer-text, #4b5563) !important; -webkit-text-fill-color: var(--emp-footer-text, #4b5563) !important; font-weight: 600;">entries</span>
                </div>
            </div>

            @if(method_exists($employees, 'total'))
            <div class="footer-center !flex !w-full !justify-center !text-center lg:!w-auto">
                <span class="pagination-info" style="color: var(--emp-footer-text, #4b5563) !important; -webkit-text-fill-color: var(--emp-footer-text, #4b5563) !important; font-weight: 600;">
                    Showing {{ ($employees->currentPage() - 1) * $employees->perPage() + 1 }}
                    - {{ min($employees->currentPage() * $employees->perPage(), $employees->total()) }}
                    of {{ $employees->total() }}
                </span>
            </div>
            @endif

            @if(method_exists($employees, 'currentPage'))
            <div class="footer-right !flex !w-full !justify-center lg:!w-auto lg:!justify-end">
                <div class="pagination !flex !flex-wrap !justify-center !gap-2">
                    @if($employees->onFirstPage())
                        <button class="page-btn disabled" type="button" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @else
                        <a href="{{ $employees->previousPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @php
                        $currentPage = $employees->currentPage();
                        $lastPage = $employees->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span class="page-btn active">{{ $i }}</span>
                        @else
                            <a href="{{ $employees->url($i) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                               class="page-btn">{{ $i }}</a>
                        @endif
                    @endfor

                    @if($lastPage > $end)
                        <span class="page-dots">...</span>
                        <a href="{{ $employees->url($lastPage) . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">{{ $lastPage }}</a>
                    @endif

                    @if($employees->hasMorePages())
                        <a href="{{ $employees->nextPageUrl() . (request('per_page') ? '&per_page=' . request('per_page') : '') }}"
                           class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="page-btn disabled" type="button" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

@include('admin.employees.partials.directory-styles')
@include('admin.employees.partials.directory-presentation')

{{-- JavaScript - COMPLETELY UNCHANGED FUNCTIONALITY --}}
@push('js')
<script>
$(document).ready(function () {
    // ===== ALL EXISTING FUNCTIONALITY - 100% UNCHANGED =====
    // Store selected employee data
    let selectedEmployees = [];
    let showEntries = localStorage.getItem('employeeShowEntries') || '10';

    // Set initial show entries value
    $('#showEntries').val(showEntries);

    // Show entries change
    $('#showEntries').on('change', function() {
        const value = $(this).val();
        localStorage.setItem('employeeShowEntries', value);
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    });

    // Select all checkboxes
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        const enabledCheckboxes = $('.employee-checkbox:not(:disabled)');
        enabledCheckboxes.prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox change
    $(document).on('change', '.employee-checkbox', function() {
        updateSelectedEmployees();
        updateSelectAllCheckbox();
    });

    // Row click to select
    $(document).on('click', 'tbody tr', function(e) {
        if ($(e.target).is('input[type="checkbox"]') ||
            $(e.target).closest('.dropdown, a, button, form').length ||
            $(e.target).is('a, button, .dropdown-item, img, i')) {
            return;
        }
        const checkbox = $(this).find('.employee-checkbox:not(:disabled)');
        if (checkbox.length) {
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Update select all checkbox
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.employee-checkbox:not(:disabled)').length;
        const checkedCheckboxes = $('.employee-checkbox:checked:not(:disabled)').length;
        const selectAll = $('#selectAll');

        if (totalCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
            return;
        }

        if (checkedCheckboxes === 0) {
            selectAll.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAll.prop('checked', true).prop('indeterminate', false);
        } else {
            selectAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    // Update selected employees
    function updateSelectedEmployees() {
        selectedEmployees = [];
        $('.employee-checkbox:checked:not(:disabled)').each(function() {
            selectedEmployees.push({
                id: $(this).val(),
                data: {
                    employee_id: $(this).data('employee-id') || '-',
                    name: cleanHTML($(this).data('name')) || '-',
                    email: $(this).data('email') || '-',
                    company: cleanHTML($(this).data('company')) || '-',
                    designation: cleanHTML($(this).data('designation')) || '-',
                    reporting_to: cleanHTML($(this).data('reporting-to')) || 'N/A',
                    status: $(this).data('status') || 'N/A'
                }
            });
        });

        const selectedCount = selectedEmployees.length;
        $('#export-selected-count').text(selectedCount + ' selected');
        $('#bulk-selected-count').text(selectedCount + ' selected');

        const exportButtons = ['#export-copy', '#export-csv', '#export-excel', '#export-pdf', '#export-print'];
        exportButtons.forEach(btn => {
            $(btn).prop('disabled', selectedCount === 0);
        });

        // Stays tappable with no selection so the button explains what to do (a disabled button gave no feedback on mobile).
        $('#btn-bulk-delete').toggleClass('is-awaiting-selection', selectedCount === 0).attr('aria-disabled', selectedCount === 0 ? 'true' : 'false');
        $('tbody tr').removeClass('selected');
        $('.employee-checkbox:checked:not(:disabled)').closest('tr').addClass('selected');
    }

    // Clean HTML function
    function cleanHTML(text) {
        if (!text) return '';
        return String(text).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
    }

    // Export functions
    function getDataForExport(exportAll = false) {
        const data = [];
        const headers = ['Employee ID', 'Name', 'Email', 'Company', 'Designation', 'Reporting To', 'Status'];
        data.push(headers);

        if (exportAll) {
            $('.employee-checkbox:not(:disabled)').each(function() {
                data.push([
                    $(this).data('employee-id') || '-',
                    cleanHTML($(this).data('name')) || '-',
                    $(this).data('email') || '-',
                    cleanHTML($(this).data('company')) || '-',
                    cleanHTML($(this).data('designation')) || '-',
                    cleanHTML($(this).data('reporting-to')) || 'N/A',
                    $(this).data('status') || 'N/A'
                ]);
            });
        } else {
            selectedEmployees.forEach(emp => {
                data.push([
                    emp.data.employee_id,
                    emp.data.name,
                    emp.data.email,
                    emp.data.company,
                    emp.data.designation,
                    emp.data.reporting_to,
                    emp.data.status
                ]);
            });
        }
        return data;
    }

    // Copy
    $('#export-copy').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to copy.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join('\t')
        ).join('\n');
        navigator.clipboard.writeText(csvContent).then(() => {
            alert('Selected rows copied to clipboard!');
        });
    });

    // CSV
    $('#export-csv').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const csvContent = data.map(row =>
            row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `employees_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    });

    // Excel
    $('#export-excel').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Employees");
        XLSX.writeFile(wb, `employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // PDF
    $('#export-pdf').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to export.');
            return;
        }
        const data = getDataForExport(false);
        const pdfDocument = {
            pageSize: 'A4', pageOrientation: 'landscape',
            content: [{ text: 'Employee List', style: 'title' }, {
                table: { headerRows: 1, body: [data[0].map(text => ({ text, style: 'tableHeader' })), ...data.slice(1)] }
            }]
        };
        window.PmsPdfExports.styleDocument(pdfDocument);
        window.pdfMake.createPdf(pdfDocument).download(`employees_${new Date().toISOString().split('T')[0]}.pdf`);
    });

    // Print
    $('#export-print').on('click', function() {
        if (selectedEmployees.length === 0) {
            alert('Please select at least one row to print.');
            return;
        }
        window.print();
    });

    // Export all
    $('#export-all').on('click', function() {
        const totalEmployees = $('.employee-checkbox:not(:disabled)').length;
        if (totalEmployees === 0) {
            alert('No employees available to export.');
            return;
        }
        if (!confirm(`Export ALL ${totalEmployees} employees to Excel?`)) return;
        const data = getDataForExport(true);
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "All Employees");
        XLSX.writeFile(wb, `all_employees_${new Date().toISOString().split('T')[0]}.xlsx`);
    });

    // Inline Status toggle (Active <-> Inactive)
    $(document).on('click', '.js-employee-status-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        if ($btn.prop('disabled')) return;

        const employeeId = $btn.attr('data-employee-id');
        const currentStatus = $btn.attr('data-status');
        const nextStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
        const followingStatus = nextStatus === 'Active' ? 'Inactive' : 'Active';

        $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("employees.bulkUpdateStatus") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: [employeeId],
                status: nextStatus,
                auto_exit_date: 1
            },
            success: function() {
                $btn
                    .removeClass('status-active status-inactive status-unknown')
                    .addClass(nextStatus === 'Active' ? 'status-active' : 'status-inactive')
                    .attr('data-status', nextStatus)
                    .attr('title', `Click to switch status to ${followingStatus}`)
                    .find('.status-text').text(nextStatus);

                const $row = $btn.closest('tr');
                const $rowCheckbox = $row.find('.employee-checkbox');
                if ($rowCheckbox.length) {
                    $rowCheckbox.attr('data-status', nextStatus).data('status', nextStatus);
                    updateSelectedEmployees();
                }

                const archiveSlot = $row.find('.js-employee-archive-item')[0];
                if (archiveSlot) {
                    if (nextStatus === 'Inactive') {
                        const form = document.createElement('form');
                        form.action = archiveSlot.getAttribute('data-destroy-url') || '#';
                        form.method = 'POST';
                        form.onsubmit = function() {
                            return confirm('Archive this inactive employee? The data will move to Archived Employees and can be restored.');
                        };
                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        const submitBtn = document.createElement('button');
                        submitBtn.type = 'submit';
                        submitBtn.className = 'dropdown-item text-danger';
                        const archiveIcon = document.createElement('i');
                        archiveIcon.className = 'fas fa-box-archive me-2';
                        submitBtn.append(archiveIcon, document.createTextNode(' Archive Employee'));
                        form.append(csrf, methodInput, submitBtn);
                        archiveSlot.replaceChildren(form);
                    } else {
                        const lockedBtn = document.createElement('button');
                        lockedBtn.type = 'button';
                        lockedBtn.className = 'dropdown-item text-muted';
                        lockedBtn.disabled = true;
                        const lockIcon = document.createElement('i');
                        lockIcon.className = 'fas fa-lock me-2';
                        lockedBtn.append(lockIcon, document.createTextNode(' Archive only inactive'));
                        archiveSlot.replaceChildren(lockedBtn);
                    }
                }
            },
            error: function(xhr) {
                alert(xhr?.responseJSON?.message || 'Unable to update employee status.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    // Bulk archive inactive employees
    $('#btn-bulk-delete, #bulkDeleteTrigger').on('click', function() {
        let inactiveSelection = selectedEmployees.filter(emp => emp.data.status === 'Inactive');
        let selectedIds = inactiveSelection.map(emp => emp.id);
        let skippedCount = selectedEmployees.length - selectedIds.length;

        if (!selectedEmployees.length) {
            alert('Select the inactive employees to archive first: tick their checkboxes (or tap their rows), then press Archive Inactive.');
            return;
        }

        if (!selectedIds.length) {
            alert('Only inactive employees can be archived. Please select inactive employees only.');
            return;
        }

        const skipText = skippedCount > 0 ? ` ${skippedCount} active/non-inactive employee(s) will be skipped.` : '';
        if (!confirm(`Archive ${selectedIds.length} inactive employee(s)? Their data will move to Archived Employees and can be restored.${skipText}`)) return;

        $.ajax({
            url: '{{ route("employees.bulk.delete") }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                employee_ids: selectedIds
            },
            success: function(response) {
                alert(response?.message || 'Employees archived successfully');
                location.reload();
            },
            error: function(xhr) {
                alert(xhr?.responseJSON?.message || 'Failed to archive employees.');
            }
        });
    });

    // Keep Bootstrap's modal above its backdrop, outside transformed page cards.
    document.body.appendChild(document.getElementById('inviteModal'));
    function inviteNotice(message, success = false) {
        $('#inviteEmailAlert').attr('class', 'alert mx-3 mb-3 ' + (success ? 'alert-success' : 'alert-danger'))
            .attr('role', 'status').text(message).show();
    }
    $('#invite-link-tab, #invite-email-tab').on('shown.bs.tab', function() {
        $('#sendInviteBtn').toggle(this.id === 'invite-email-tab');
        $('#inviteEmailAlert').hide();
    });

    // Invite by email
    $('#inviteEmailForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#inviteEmail').val().trim();
        const message = $('#inviteMessage').val().trim();
        if (!this.reportValidity() || $('#sendInviteBtn').prop('disabled')) return;
        $('#inviteEmailAlert').hide();
        $('#sendInviteBtn').prop('disabled', true);
        $('#sendInviteSpinner').removeClass('d-none');

        $.ajax({
            url: '{{ route("employees.sendInvite") }}',
            type: 'POST',
            timeout: 30000,
            data: {
                _token: '{{ csrf_token() }}',
                email: email,
                message: message
            },
            success: function(res) {
                inviteNotice(res.message, true);
                $('#inviteEmailForm')[0].reset();
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                inviteNotice(errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Could not send the invitation. Check your connection and try again.'));
            },
            complete: function() {
                $('#sendInviteBtn').prop('disabled', false);
                $('#sendInviteSpinner').addClass('d-none');
            }
        });
    });

    // Generate invite link
    $('#createLinkBtn').on('click', function() {
        const input = document.getElementById('inviteLinkEmail');
        if (!input.reportValidity() || $(this).prop('disabled')) return;
        $('#linkContainer').hide();
        $('#inviteLink').val('');
        $('#inviteEmailAlert').hide();
        $(this).prop('disabled', true);
        $.ajax({
            url: '{{ route("employees.inviteLink") }}',
            type: 'POST',
            timeout: 30000,
            data: { _token: '{{ csrf_token() }}', email: input.value.trim() },
            success: function(res) {
                $('#inviteLink').val(res.url);
                $('#linkContainer').show();
                inviteNotice(res.message, true);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                inviteNotice(errors ? Object.values(errors).flat().join(' ') : (xhr.responseJSON?.message || 'Could not create the invitation link. Please try again.'));
            },
            complete: function() { $('#createLinkBtn').prop('disabled', false); }
        });
    });

    // Copy link
    $('#copyLinkBtn').on('click', async function() {
        const link = $('#inviteLink').val();
        if (!link) return;
        try {
            if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(link);
            else {
                $('#inviteLink').select();
                if (!document.execCommand('copy')) throw new Error('Copy failed');
            }
            inviteNotice('Invitation link copied.', true);
        } catch (error) {
            inviteNotice('Select and copy the invitation link manually.');
        }
    });

    // Share link
    $('#shareLinkBtn').on('click', function() {
        const link = $('#inviteLink').val();
        window.location.href = `mailto:?subject=${encodeURIComponent(@json('Join ' . (session('current_company_name') ?: config('app.name'))))}&body=${encodeURIComponent(link)}`;
    });

    // Blocked delete
    $(document).on('click', '.blocked-delete-btn', function (e) {
        e.preventDefault();
        const name = $(this).data('employee-name');
        const id = $(this).data('employee-id');
        const subordinateCount = $(this).data('subordinate-count');
        $('#blockedEmployeeName').text(name);
        $('#blockedEmployeeReason').text(`Cannot archive because ${subordinateCount} team member(s) report to this employee.`);
        $('#blocked-view-subordinates').attr('href', '{{ url("/") }}/employees/' + id);
        new bootstrap.Modal(document.getElementById('blockedDeleteModal')).show();
    });

    // ===== Delete employee (permanent) — only after the employee is inactive =====
    const deleteModalEl = document.getElementById('employeeDeleteModal');
    const inactiveModalEl = document.getElementById('employeeInactiveModal');
    // Move lifecycle and action modals to document.body so they sit above Bootstrap's backdrop.
    ['blockedDeleteModal', 'employeeDeleteModal', 'employeeInactiveModal', 'quickResetPasswordModal'].forEach(id => {
        const modalEl = document.getElementById(id);
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
    });
    const initialsOf = name => String(name || 'E').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase() || 'E';
    let deleteContext = null;

    function openInactiveModal(data) {
        $('#employeeInactiveName').text(data.name);
        $('#employeeInactiveMeta').text(data.code ? 'Employee ID ' + data.code : '');
        $('#employeeInactiveInitials').text(initialsOf(data.name));
        $('#employeeInactiveForm').attr('action', data.inactiveUrl);
        bootstrap.Modal.getOrCreateInstance(inactiveModalEl).show();
    }

    $(document).on('click', '.employee-inactive-trigger', function (e) {
        e.preventDefault();
        openInactiveModal({ name: $(this).data('employee-name'), code: $(this).data('employee-code'), inactiveUrl: $(this).data('inactive-url') });
    });

    $(document).on('click', '.employee-delete-trigger', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const state = String($btn.data('delete-state'));
        const team = parseInt($btn.data('employee-team'), 10) || 0;
        const code = String($btn.data('employee-code') || '').trim();
        deleteContext = { name: String($btn.data('employee-name')), code, inactiveUrl: $btn.data('inactive-url') };
        const expected = code || deleteContext.name;
        const ready = state === 'ready';

        $('#employeeDeleteName').text(deleteContext.name);
        $('#employeeDeleteMeta').text([code ? 'ID ' + code : null, $btn.data('employee-role'), $btn.data('employee-department')].filter(Boolean).join(' · '));
        $('#employeeDeleteInitials').text(initialsOf(deleteContext.name));
        $('#employeeDeleteStatus').text(state === 'active' ? 'Active' : (state === 'privileged' ? 'HR / Manager' : 'Inactive')).toggleClass('is-active', state === 'active');
        $('#employeeDeleteStepInactive').toggleClass('is-done', state !== 'active' && state !== 'privileged').toggleClass('is-todo', state === 'active');
        $('#employeeDeleteStepTeam').toggleClass('is-done', team === 0).toggleClass('is-todo', team > 0)
            .find('span:last').text(team > 0 ? team + ' team member(s) still report to this employee' : 'No team members report to this employee');

        const reasons = {
            active: deleteContext.name + ' is still active. Mark the employee inactive first — then the Delete option unlocks.',
            'has-team': team + ' team member(s) report to ' + deleteContext.name + '. Assign them a new reporting manager first, then delete.',
            privileged: deleteContext.name + ' has HR or manager access. Only employee accounts can be deleted from the Employees section.',
        };
        $('#employeeDeleteTitle').text(ready ? 'Delete employee permanently?' : 'Delete is locked');
        $('#employeeDeleteSubtitle').text(ready ? 'Review the details below before you continue.' : 'Complete the steps below to unlock it.');
        $('#employeeDeleteIcon').toggleClass('is-locked', !ready).find('i').attr('class', 'fas ' + (ready ? 'fa-trash-can' : 'fa-lock'));
        $('#employeeDeleteLocked').toggle(!ready).find('p').text(reasons[state] || '');
        $('#employeeDeleteForm').toggle(ready).attr('action', ready ? $btn.data('delete-url') : '#');
        $('#employeeDeleteCode').text(expected);
        $('#employeeDeleteConfirm').val('').attr('placeholder', expected).data('expected', expected);
        $('#employeeDeleteSubmit').prop('hidden', !ready).toggle(ready).prop('disabled', true);
        $('#employeeDeleteMakeInactive').prop('hidden', state !== 'active').toggle(state === 'active');
        bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    });

    $('#employeeDeleteConfirm').on('input', function () {
        const matches = this.value.trim().toLowerCase() === String($(this).data('expected') || '').trim().toLowerCase();
        $('#employeeDeleteSubmit').prop('disabled', !matches);
        this.classList.toggle('is-match', matches);
    });
    deleteModalEl?.addEventListener('shown.bs.modal', () => { if ($('#employeeDeleteForm').is(':visible')) $('#employeeDeleteConfirm').trigger('focus'); });
    $('#employeeDeleteForm').on('submit', function (e) {
        if ($('#employeeDeleteSubmit').prop('disabled')) { e.preventDefault(); return; }
        $('#employeeDeleteSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Deleting…');
    });
    $('#employeeDeleteMakeInactive').on('click', function () {
        const context = deleteContext;
        if (!context || !deleteModalEl) return;
        if (!deleteModalEl.classList.contains('show')) {
            openInactiveModal(context);
            return;
        }
        deleteModalEl.addEventListener('hidden.bs.modal', () => openInactiveModal(context), { once: true });
        bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
    });
    $('#employeeInactiveForm').on('submit', function () {
        $('#employeeInactiveSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Saving…');
    });

    // Initialize
    updateSelectedEmployees();
    updateSelectAllCheckbox();

    // Mobile labels
    $('#employeeTable thead th').each(function(index) {
            const headerText = $(this).text().trim();
            $('#employeeTable tbody tr').each(function() {
                $(this).find('td:not([colspan])').eq(index).attr('data-label', headerText);
            });
    });
});
</script>

<!-- Quick Password Reset Modal -->
<div class="modal fade" id="quickResetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-key text-warning me-2"></i> Reset Employee Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickResetPasswordForm" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">Resetting password for <strong id="quickResetEmployeeName" class="text-dark">Employee</strong>. They will receive an on-screen notification and be automatically logged out after 1 minute.</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">New Password</label>
                        <input type="password" name="new_password" class="form-control rounded-3" placeholder="Enter new password (min 8 chars)" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control rounded-3" placeholder="Confirm new password" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold rounded-pill px-4">
                        <i class="fas fa-lock me-1"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetPasswordModal(id, name) {
    const modalEl = document.getElementById('quickResetPasswordModal');
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
    document.getElementById('quickResetEmployeeName').textContent = name;
    document.getElementById('quickResetPasswordForm').action = "{{ url('/users') }}/" + id + "/change-password";
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@endpush

@endsection
