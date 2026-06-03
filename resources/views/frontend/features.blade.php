@extends('frontend.layouts-frontend.app')

@section('title', 'Features - BBH PMS')

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="hero-badge">
                    <i class="fas fa-layer-group"></i>
                    <span>Complete PMS Toolkit</span>
                </div>
                <h1 class="hero-title">
                    Features built for <span class="gradient-text">focused teams</span>
                </h1>
                <p class="hero-description">
                    Plan work, track progress, manage people, and measure delivery from one connected project management system.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-purple btn-glow btn-lg">
                        Get Started Free <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('pricing') }}" class="btn btn-outline-purple btn-lg">
                        <i class="fas fa-tags"></i> View Pricing
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-image-wrapper">
                    <img src="{{ asset('frontend/img/hero-dashboard.png') }}" alt="BBH PMS feature dashboard" class="hero-image">
                    <div class="floating-card card-1">
                        <i class="fas fa-check-circle"></i>
                        <span>Milestones on track</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-chart-line"></i>
                        <span>Live team insights</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Core Features</span>
            <h2>Everything your team needs to deliver</h2>
            <p>Bring planning, execution, reporting, and people operations into one practical workspace.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Task Management</h3>
                    <p>Create tasks, assign owners, set priorities, and keep every responsibility visible.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-columns"></i>
                    </div>
                    <h3>Kanban Boards</h3>
                    <p>Move work through custom stages with a clear visual workflow for every project.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Gantt Timelines</h3>
                    <p>Map dependencies, deadlines, and milestones before delivery risks surprise you.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Attendance Tracking</h3>
                    <p>Monitor attendance, working hours, and team availability with less manual effort.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Leave Management</h3>
                    <p>Handle leave requests, approvals, and balances without losing project visibility.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Reports & Analytics</h3>
                    <p>Turn project activity into dashboards and reports your managers can act on quickly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-xxl py-5 bg-light">
    <div class="container px-lg-5">
        <div class="section-title" data-aos="fade-up">
            <span class="section-subtitle">Why It Works</span>
            <h2>Built around daily operations</h2>
            <p>BBH PMS keeps the practical work close: updates, approvals, ownership, and visibility.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Team Clarity</h3>
                    <p>Everyone knows what they own, what is blocked, and what comes next.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure Access</h3>
                    <p>Keep company data protected with controlled access for the right people.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Responsive UI</h3>
                    <p>Review work, approve requests, and stay current from desktop or mobile.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="service-item">
                    <div class="service-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Reliable Support</h3>
                    <p>Get help when your team needs guidance, setup support, or workflow advice.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-xxl py-5">
    <div class="container px-lg-5 text-center" data-aos="fade-up">
        <div class="section-title mb-4">
            <span class="section-subtitle">Ready To Start</span>
            <h2>Bring your projects into one workspace</h2>
            <p>Start with the essentials and scale your process as your team grows.</p>
        </div>
        <a href="{{ route('register') }}" class="btn btn-purple btn-glow btn-lg">
            Create Your Account <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
@endsection
