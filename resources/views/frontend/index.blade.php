@extends('frontend.layouts-frontend.app')

@section('title', 'Bitroxia PMS - Project, HR and Team Management Software')
@section('meta_description', 'Bitroxia PMS is a colorful, secure project management and HR software platform for tasks, attendance, leave, tickets, clients, contracts, analytics, and team reporting.')
@section('meta_keywords', 'Bitroxia PMS, project management software, HR software, task management, attendance tracking, leave management, ticket management, Gantt chart, Kanban board, business dashboard')

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-badge">
                    <i class="fas fa-layer-group"></i>
                    <span>Project operations, HR, and reporting in one place</span>
                </div>
                <h1 class="hero-title">
                    A realistic PMS workspace for <span class="gradient-text">fast-moving teams</span>
                </h1>
                <p class="hero-description">
                    Bitroxia PMS keeps tasks, timelines, attendance, leave, tickets, clients, and reports connected so managers can see work clearly and teams can move without confusion.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('login') }}" class="btn btn-purple btn-lg">
                        Login <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('features') }}" class="btn btn-outline-purple btn-lg">
                        <i class="fas fa-table-cells-large"></i> Explore Features
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number counter">120</span>
                        <span class="stat-label">Daily workflows</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number counter">18</span>
                        <span class="stat-label">Core modules</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number counter">99</span>
                        <span class="stat-label">Responsive UI</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="150">
                <div class="hero-image-wrapper">
                    <img src="{{ asset('frontend/img/bitroxia-pms-hero.png') }}" alt="Bitroxia PMS dashboard interface with project management analytics" class="hero-image">
                    <div class="floating-card card-1">
                        <i class="fas fa-check-circle"></i>
                        <span>124 tasks completed</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-users"></i>
                        <span>Team availability live</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="brand-marquee" aria-label="Bitroxia PMS highlights">
    <div class="marquee-track">
        @foreach(['Projects', 'Tasks', 'Kanban', 'Gantt', 'Attendance', 'Leave', 'Tickets', 'Clients', 'Contracts', 'Reports', 'Analytics', 'HR'] as $item)
            <span>{{ $item }}</span>
        @endforeach
        @foreach(['Projects', 'Tasks', 'Kanban', 'Gantt', 'Attendance', 'Leave', 'Tickets', 'Clients', 'Contracts', 'Reports', 'Analytics', 'HR'] as $item)
            <span>{{ $item }}</span>
        @endforeach
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Operational Control</span>
            <h2>Everything a project team checks every day</h2>
            <p>Bitroxia PMS brings delivery, people, time, approvals, and reporting into a single practical system.</p>
        </div>
        <div class="row g-4">
            @php
                $features = [
                    ['icon' => 'fa-diagram-project', 'title' => 'Projects & Milestones', 'text' => 'Plan delivery, split work into phases, assign owners, and keep project progress visible.'],
                    ['icon' => 'fa-list-check', 'title' => 'Tasks & Kanban', 'text' => 'Track priorities, comments, files, labels, subtasks, and status changes without scattered updates.'],
                    ['icon' => 'fa-clock', 'title' => 'Attendance & Timelogs', 'text' => 'Record clock-ins, work hours, locations, task timers, and timesheets from one workflow.'],
                    ['icon' => 'fa-calendar-check', 'title' => 'Leave & Holidays', 'text' => 'Manage leave policies, balances, holiday calendars, and approvals while protecting project capacity.'],
                    ['icon' => 'fa-ticket', 'title' => 'Tickets & Clients', 'text' => 'Handle support issues, client records, lead contacts, deals, and project communication in context.'],
                    ['icon' => 'fa-chart-pie', 'title' => 'Dashboards & Reports', 'text' => 'Use dashboards, exports, analytics, and activity logs to understand delivery health quickly.'],
                ];
            @endphp
            @foreach($features as $index => $feature)
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 80) }}">
                    <div class="feature-card">
                        <div class="card-icon">
                            <i class="fas {{ $feature['icon'] }}"></i>
                        </div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['text'] }}</p>
                        <a href="{{ route('features') }}" class="card-link">View details <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="visual-story-section">
    <div class="container px-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="visual-frame">
                    <img src="{{ asset('frontend/img/bitroxia-workspace-visual.png') }}" alt="Colorful Bitroxia PMS project management workspace visual">
                    <div class="visual-chip chip-a"><i class="fas fa-bolt"></i> Live work pulse</div>
                    <div class="visual-chip chip-b"><i class="fas fa-shield-halved"></i> Secure roles</div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="section-title text-start mx-0 mb-4">
                    <span class="section-subtitle">Colorful Workspace</span>
                    <h2>Beautiful screens for serious daily work</h2>
                    <p>Bitroxia PMS uses clean visual hierarchy, bright status colors, animated feedback, and focused dashboards so managers can scan faster and employees can act with confidence.</p>
                </div>
                <div class="experience-list">
                    @foreach([
                        ['fa-wand-magic-sparkles', 'Animated dashboard states', 'Subtle motion helps important information feel alive without distracting users.'],
                        ['fa-palette', 'Balanced color system', 'Blue, cyan, violet, magenta, and emerald accents separate modules and action states.'],
                        ['fa-mobile-screen-button', 'Responsive by design', 'Every public section and app entry point is designed to stay polished on mobile.'],
                    ] as $item)
                        <div class="experience-item">
                            <span><i class="fas {{ $item[0] }}"></i></span>
                            <div>
                                <h3>{{ $item[1] }}</h3>
                                <p>{{ $item[2] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-xxl py-5 bg-light">
    <div class="container px-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="section-title text-start mx-0 mb-4">
                    <span class="section-subtitle">How It Works</span>
                    <h2>Built for the rhythm of real teams</h2>
                    <p>From planning to payroll-adjacent attendance records, every module supports the day-to-day operating loop.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-purple btn-lg">Login <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    @php
                        $steps = [
                            ['Plan', 'Create projects, milestones, tasks, labels, and dependencies.'],
                            ['Assign', 'Add teams, clients, departments, roles, and clear responsibilities.'],
                            ['Track', 'Monitor task status, attendance, timelogs, leaves, tickets, and files.'],
                            ['Report', 'Use dashboards, exports, analytics, and activity logs to make decisions.'],
                        ];
                    @endphp
                    @foreach($steps as $index => $step)
                        <div class="col-md-6" data-aos="zoom-in" data-aos-delay="{{ 100 + ($index * 80) }}">
                            <div class="workflow-card">
                                <div class="card-icon">
                                    <span>{{ $index + 1 }}</span>
                                </div>
                                <h3>{{ $step[0] }}</h3>
                                <p class="mb-0">{{ $step[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="metrics-section">
    <div class="container px-lg-5">
        <div class="metrics-grid">
            @foreach([
                ['24/7', 'Operational visibility', 'Project, HR, attendance, and ticket records stay available whenever your team needs them.'],
                ['360', 'Degree workspace', 'Tasks, clients, people, contracts, files, approvals, and reports connect into one view.'],
                ['Zero', 'Template confusion', 'Every public page now uses one consistent brand system and SEO structure.'],
            ] as $metric)
                <div class="metric-card" data-aos="zoom-in">
                    <strong>{{ $metric[0] }}</strong>
                    <h3>{{ $metric[1] }}</h3>
                    <p>{{ $metric[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">For Every Department</span>
            <h2>One interface, many business functions</h2>
            <p>The UI stays clean while supporting project delivery, HR operations, finance records, tickets, and reporting.</p>
        </div>
        <div class="row g-4">
            @php
                $services = [
                    ['fa-users-gear', 'Managers', 'Plan capacity, review project health, approve work, and spot blockers early.'],
                    ['fa-user-tie', 'HR Teams', 'Track employees, departments, designations, attendance, leaves, holidays, and awards.'],
                    ['fa-handshake', 'Client Teams', 'Manage clients, lead contacts, deals, tickets, contracts, and project communication.'],
                ];
            @endphp
            @foreach($services as $index => $service)
                <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="{{ 100 + ($index * 100) }}">
                    <div class="service-item text-start">
                        <div class="service-icon">
                            <i class="fas {{ $service[0] }}"></i>
                        </div>
                        <h3>{{ $service[1] }}</h3>
                        <p>{{ $service[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="container-xxl py-5 bg-light">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Connected Platform</span>
            <h2>Works across your whole business flow</h2>
            <p>Keep your current functionality intact while making the frontend feel like a standard modern software product.</p>
        </div>
        <div class="integration-grid">
            @foreach([
                ['fa-file-excel', 'Exports', 'Attendance, leads, reports, timelogs'],
                ['fa-envelope-open-text', 'Notifications', 'Tasks, tickets, employees, clock-ins'],
                ['fa-map-location-dot', 'Location', 'Attendance map and team visibility'],
                ['fa-user-shield', 'Roles', 'Admin, employee, client, superadmin'],
                ['fa-database', 'Records', 'Projects, clients, contracts, payments'],
                ['fa-chart-area', 'Insights', 'Dashboards, audit logs, analytics'],
            ] as $index => $item)
                <div class="integration-card" data-aos="fade-up" data-aos-delay="{{ 80 + ($index * 60) }}">
                    <i class="fas {{ $item[0] }}"></i>
                    <h3>{{ $item[1] }}</h3>
                    <p>{{ $item[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="testimonial-section">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle" style="background: rgba(255,255,255,0.14); color: white; border-color: rgba(255,255,255,0.2);">Proof Points</span>
            <h2>Designed for clarity under pressure</h2>
            <p class="text-white-50">Bitroxia PMS focuses on the screens teams actually need: lists, boards, dashboards, approvals, and audit-friendly records.</p>
        </div>
        <div class="owl-carousel testimonial-carousel" data-aos="fade-up" data-aos-delay="100">
            @php
                $quotes = [
                    ['Project Director', 'We can see delivery status, assigned owners, and pending actions without asking five different people for updates.'],
                    ['HR Lead', 'Attendance, leave, employees, and holidays live beside project data, so planning is much more practical.'],
                    ['Operations Manager', 'The dashboards give our team enough detail to act quickly without drowning everyone in reports.'],
                    ['Client Success Lead', 'Tickets, files, comments, and client records stay connected to the work that caused them.'],
                ];
            @endphp
            @foreach($quotes as $index => $quote)
                <div class="testimonial-card">
                    <i class="fas fa-quote-left"></i>
                    <p>"{{ $quote[1] }}"</p>
                    <div class="testimonial-author">
                        <div class="avatar">
                            <img src="{{ asset('frontend/img/testimonial-' . (($index % 4) + 1) . '.jpg') }}" alt="{{ $quote[0] }}">
                        </div>
                        <div class="author-info">
                            <h5>{{ $quote[0] }}</h5>
                            <span>Bitroxia PMS user</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="contact-section" id="contact">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Contact</span>
            <h2>Talk to us about your PMS workflow</h2>
            <p>Need project management, attendance, HR, ticket, client, or reporting customization? Send your requirement and preview the business location map.</p>
        </div>
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="contact-panel">
                    <h3>Let us build the right workspace</h3>
                    <p>Share what your team needs. We will help align modules, roles, reports, and frontend presentation.</p>
                    <div class="contact-list">
                        <a href="mailto:info@bitroxia.com"><i class="fas fa-envelope"></i> info@bitroxia.com</a>
                        <a href="tel:+910000000000"><i class="fas fa-phone"></i> +91 00000 00000</a>
                        <span><i class="fas fa-location-dot"></i> India and global remote delivery</span>
                    </div>
                    <form class="contact-mini-form" action="{{ route('company.contact.submit') }}" method="POST">
                        @csrf
                        <input type="text" name="name" placeholder="Your name" required>
                        <input type="email" name="email" placeholder="Work email" required>
                        <textarea name="message" rows="4" placeholder="Tell us about your project" required></textarea>
                        <button type="submit">Send Message <i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="map-panel">
                    <iframe
                        title="Bitroxia PMS location map"
                        src="https://www.google.com/maps?q=India&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content" data-aos="fade-up">
            <h2>Bring your team into one workspace</h2>
            <p>Start with projects and tasks, then add HR, attendance, leave, tickets, clients, reports, and finance workflows as your team grows.</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your work email" required>
                <button type="submit">Request Access <i class="fas fa-arrow-right"></i></button>
            </form>
            <p class="mt-3 small text-white-50">Responsive web app. Secure login. Built for daily operations.</p>
        </div>
    </div>
</section>
@endsection
