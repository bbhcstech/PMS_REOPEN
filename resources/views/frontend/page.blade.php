@extends('frontend.layouts-frontend.app')

@section('title', $page['title'] . ' - Bitroxia PMS')
@section('meta_description', $page['description'])
@section('meta_keywords', 'Bitroxia PMS ' . $page['title'] . ', ' . $page['eyebrow'] . ', project management software, HR management software, attendance tracking, leave management, business dashboard')

@section('content')
<section class="page-hero">
    <div class="container">
        <button type="button" class="frontend-back-btn" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='{{ route('features') }}'; }">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
        </button>
        <div class="hero-badge">
            <i class="fas {{ $page['icon'] }}"></i>
            <span>{{ $page['eyebrow'] }}</span>
        </div>
        <h1>{{ $page['heading'] }}</h1>
        <p>{{ $page['description'] }}</p>
        <div class="hero-buttons mt-4">
            <a href="{{ route('login') }}" class="btn btn-purple btn-lg">Login <i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('features') }}" class="btn btn-outline-purple btn-lg">View Features</a>
        </div>
    </div>
</section>

@if(($page['type'] ?? null) === 'pricing')
    <section class="container-xxl py-5">
        <div class="container px-lg-5">
            <div class="section-title" data-aos="fade-up">
                <span class="section-subtitle">Pricing</span>
                <h2>Flexible plans for growing teams</h2>
                <p>Choose the workspace size and modules your business needs today, then scale as operations mature.</p>
            </div>
            <div class="row g-4">
                @php
                    $plans = [
                        ['Starter', '$0', 'For trying project and task basics', ['Projects and task lists', 'Team collaboration', 'Responsive dashboard', 'Basic reports']],
                        ['Business', '$29', 'For active teams running daily operations', ['Projects, tasks, HR and attendance', 'Leave and holiday management', 'Tickets and clients', 'Exports and reports']],
                        ['Enterprise', 'Custom', 'For companies needing full control', ['Role-aware administration', 'Contracts and finance workflows', 'Advanced settings', 'Priority implementation support']],
                    ];
                @endphp
                @foreach($plans as $index => $plan)
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 100) }}">
                        <div class="pricing-card {{ $index === 1 ? 'featured' : '' }}">
                            <h3>{{ $plan[0] }}</h3>
                            <div class="price">{{ $plan[1] }}</div>
                            <p>{{ $plan[2] }}</p>
                            <ul>
                                @foreach($plan[3] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ route('login') }}" class="btn {{ $index === 1 ? 'btn-purple' : 'btn-outline-purple' }} w-100">Login</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@elseif(($page['type'] ?? null) === 'legal')
    <section class="container-xxl py-5">
        <div class="container px-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="page-panel">
                        <div class="card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h2>{{ $page['title'] }}</h2>
                        @if(!empty($page['effective_date']))
                            <p class="text-muted mb-4">Effective date: {{ \Carbon\Carbon::parse($page['effective_date'])->format('d M Y') }}</p>
                        @endif
                        <div class="legal-content">
                            {!! nl2br(e($page['legal_content'] ?? 'Terms will be updated soon.')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@else
    <section class="container-xxl py-5">
        <div class="container px-lg-5">
            <div class="section-title" data-aos="fade-up">
                <span class="section-subtitle">{{ $page['eyebrow'] }}</span>
                <h2>Built into the Bitroxia PMS operating system</h2>
                <p>Use this area alongside projects, employees, tasks, tickets, attendance, reporting, and admin controls.</p>
            </div>
            <div class="row g-4">
                @php
                    $items = [
                        ['fa-eye', 'Clear Visibility', 'See ownership, status, timing, and progress from screens designed for quick review.'],
                        ['fa-users-gear', 'Team Alignment', 'Keep managers, employees, clients, and admins working from one source of truth.'],
                        ['fa-chart-simple', 'Useful Reporting', 'Turn everyday activity into dashboards, exports, and records that support decisions.'],
                    ];
                @endphp
                @foreach($items as $index => $item)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 100 + ($index * 100) }}">
                        <div class="page-panel">
                            <div class="card-icon">
                                <i class="fas {{ $item[0] }}"></i>
                            </div>
                            <h3>{{ $item[1] }}</h3>
                            <p class="mb-0">{{ $item[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container-xxl py-5 bg-light">
        <div class="container px-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="{{ asset('frontend/img/bitroxia-pms-hero.png') }}" class="hero-image" alt="Bitroxia PMS workspace preview">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="section-title text-start mx-0 mb-4">
                        <span class="section-subtitle">Connected Workflow</span>
                        <h2>Designed to work with the rest of your system</h2>
                        <p>{{ $page['description'] }}</p>
                    </div>
                    <ul class="check-list">
                        <li>Responsive interface for desktop and mobile teams.</li>
                        <li>Role-aware navigation for admins, employees, clients, and superadmins.</li>
                        <li>Connected records across tasks, people, files, approvals, and reports.</li>
                        <li>SEO-ready public pages with consistent branding and clear page metadata.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endif

@if(($page['title'] ?? '') === 'Contact')
    <section class="contact-section" id="contact">
        <div class="container px-lg-5">
            <div class="section-title" data-aos="fade-up">
                <span class="section-subtitle">Contact</span>
                <h2>Send your requirement to Bitroxia PMS</h2>
                <p>Use the form below for project management, HR, attendance, ticket, client, contract, or reporting requirements.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="contact-panel">
                        <h3>Business inquiry</h3>
                        <p>Tell us what your team needs and we will respond with a practical direction.</p>
                        <div class="contact-list">
                            <a href="mailto:info@bitroxia.com"><i class="fas fa-envelope"></i> info@bitroxia.com</a>
                            <a href="tel:+910000000000"><i class="fas fa-phone"></i> +91 00000 00000</a>
                            <span><i class="fas fa-location-dot"></i> India and global remote delivery</span>
                        </div>
                        <form class="contact-mini-form" action="{{ route('company.contact.submit') }}" method="POST">
                            @csrf
                            <input type="text" name="name" placeholder="Your name" required>
                            <input type="email" name="email" placeholder="Work email" required>
                            <textarea name="message" rows="4" placeholder="Message" required></textarea>
                            <button type="submit">Send Message <i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="map-panel">
                        <iframe
                            title="Bitroxia PMS Google Map"
                            src="https://www.google.com/maps?q=India&output=embed"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif

<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content" data-aos="fade-up">
            <h2>Ready to make work easier to manage?</h2>
            <p>Start using Bitroxia PMS for project delivery, HR operations, attendance, tickets, clients, and reporting.</p>
            <a href="{{ route('login') }}" class="btn btn-purple btn-lg">Login <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
@endsection
