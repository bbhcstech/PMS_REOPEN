@extends('frontend.layouts-frontend.app')

@section('title', 'Bitroxia PMS Features - Projects, HR, Attendance and Reports')
@section('meta_description', 'Explore Bitroxia PMS features for project management, task boards, attendance, leave, tickets, clients, contracts, reports, and admin controls.')
@section('meta_keywords', 'Bitroxia PMS features, project management features, attendance tracking, HRMS, task board, Gantt chart, reports')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="hero-badge">
            <i class="fas fa-layer-group"></i>
            <span>Complete PMS toolkit</span>
        </div>
        <h1>Features built around actual daily operations</h1>
        <p>Bitroxia PMS connects project delivery, HR records, attendance, leave, tickets, clients, contracts, and reporting in one responsive business workspace.</p>
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Core Modules</span>
            <h2>Run the full project lifecycle</h2>
            <p>Each module is designed to keep ownership clear, reduce manual follow-up, and make reporting easier.</p>
        </div>
        <div class="row g-4">
            @php
                $modules = [
                    ['fa-diagram-project', 'Project Workspace', 'Projects, milestones, members, discussions, notes, files, expenses, public Gantt views, and project activity logs.'],
                    ['fa-list-check', 'Task Operations', 'Task lists, Kanban boards, calendar views, subtasks, labels, comments, files, notes, timers, approvals, and history.'],
                    ['fa-clock', 'Time & Attendance', 'Clock-in/out, location tracking, employee timelines, attendance reports, task timers, and timesheet exports.'],
                    ['fa-calendar-check', 'Leave & Holidays', 'Leave policies, balances, approvals, holiday calendars, waiting approvals, and employee self-service views.'],
                    ['fa-users', 'Employee Management', 'Employees, departments, parent departments, designations, archives, profiles, awards, and role-aware access.'],
                    ['fa-headset', 'Tickets & Support', 'Ticket groups, replies, status changes, attachments, bulk actions, and support activity tracking.'],
                    ['fa-handshake', 'CRM & Clients', 'Client categories, subcategories, lead contacts, deals, follow-ups, conversion workflows, and imports/exports.'],
                    ['fa-file-signature', 'Contracts & Finance', 'Contracts, templates, signatures, expenses, invoices, payments, business addresses, and company settings.'],
                    ['fa-chart-line', 'Dashboards & Reports', 'Project, HR, ticket, client, attendance, timelog, export, analytics, and activity reporting screens.'],
                ];
            @endphp
            @foreach($modules as $index => $module)
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 80 + ($index * 50) }}">
                    <div class="feature-card">
                        <div class="card-icon">
                            <i class="fas {{ $module[0] }}"></i>
                        </div>
                        <h3>{{ $module[1] }}</h3>
                        <p>{{ $module[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="container-xxl py-5 bg-light">
    <div class="container px-lg-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="{{ asset('frontend/img/bitroxia-pms-hero.png') }}" class="hero-image" alt="Bitroxia PMS project management dashboard preview">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="section-title text-start mx-0 mb-4">
                    <span class="section-subtitle">Why Teams Use It</span>
                    <h2>Less scattered work, more visible progress</h2>
                    <p>Bitroxia PMS gives managers a single control point while keeping everyday screens simple for employees and clients.</p>
                </div>
                <ul class="check-list">
                    <li>Role-aware dashboards for admins, employees, clients, and super admins.</li>
                    <li>Responsive UI that works across desktop, tablet, and mobile screens.</li>
                    <li>Structured exports for attendance, timelogs, leads, contracts, and reports.</li>
                    <li>Audit-friendly activity tracking for tasks, projects, tickets, and users.</li>
                    <li>Settings for company profile, app behavior, file uploads, maps, and custom profile fields.</li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-purple btn-lg">Login <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Built For Use</span>
            <h2>Practical controls your users expect</h2>
            <p>The interface supports repeated daily actions without turning the frontend into a noisy brochure.</p>
        </div>
        <div class="row g-4">
            @php
                $controls = [
                    ['fa-filter', 'Filters & Bulk Actions', 'Find records quickly and update multiple items when operations move fast.'],
                    ['fa-file-export', 'Imports & Exports', 'Move lead, attendance, timelog, and report data through useful file formats.'],
                    ['fa-bell', 'Notifications', 'Keep users aware of assignments, employee creation, ticket activity, and clock-in events.'],
                    ['fa-shield-halved', 'Access Control', 'Separate admin, employee, client, and superadmin workflows with guarded routes.'],
                ];
            @endphp
            @foreach($controls as $index => $control)
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="{{ 100 + ($index * 80) }}">
                    <div class="service-item text-start">
                        <div class="service-icon">
                            <i class="fas {{ $control[0] }}"></i>
                        </div>
                        <h3>{{ $control[1] }}</h3>
                        <p>{{ $control[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content" data-aos="fade-up">
            <h2>Ready to organize your project operations?</h2>
            <p>Use Bitroxia PMS to align tasks, teams, time, tickets, HR, and reporting in one clear workspace.</p>
            <a href="{{ route('login') }}" class="btn btn-purple btn-lg">Login <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
@endsection
