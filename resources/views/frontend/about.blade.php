@extends('frontend.layouts-frontend.app')

@section('title', 'About Us — Bitroxia PMS')
@section('meta_description', 'The story, principles and teams behind Bitroxia PMS.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Company</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>About Us</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Company</span>
        <h1>Building the operating system for fast-moving teams</h1>
        <p>Bitroxia PMS started from a simple observation: teams don't fail because they lack tools — they fail because their tools don't talk to each other. We build one connected workspace instead.</p>
      </div>
    </div>
  </section>

  <!-- Our Story -->
  <section class="section">
    <div class="container">
      <div class="content-block" data-reveal>
        <span class="eyebrow">Our Story</span>
        <h2>One workspace, built from real operating pain</h2>
        <p>Most growing teams end up running projects in one tool, attendance in another, tickets in a third, and reporting in a spreadsheet that's always a week out of date. Bitroxia PMS was built to close those gaps — connecting delivery, people and client data so managers get one honest picture instead of five partial ones.</p>
        <p>Every module ships together by design. HR data informs project capacity planning. Ticket history stays tied to the project that caused it. Reports pull from the same live records your team already updates every day — nothing is a manual export away from being wrong.</p>
      </div>
    </div>
  </section>

  <!-- Principles -->
  <section class="section section-soft">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">What We Value</span>
        <h2>Principles behind every release</h2>
      </div>
      <div class="pillars-grid" data-reveal-group>
        <div class="pillar-card" data-reveal style="--i:0">
          <div class="pillar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/></svg>
          </div>
          <h3>Reliability First</h3>
          <p>Records stay accurate and available — audit-friendly by default, not as an afterthought.</p>
        </div>
        <div class="pillar-card" data-reveal style="--i:1">
          <div class="pillar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5M3 16.5l9 5 9-5"/></svg>
          </div>
          <h3>Clarity Over Clutter</h3>
          <p>Every screen answers one question well instead of trying to show everything at once.</p>
        </div>
        <div class="pillar-card" data-reveal style="--i:2">
          <div class="pillar-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="3"/><path d="M2 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 3.1a4 4 0 010 7.8M22 20c0-2.8-2-5.2-4.7-6.4"/></svg>
          </div>
          <h3>Built for Real Teams</h3>
          <p>Features ship because operators asked for them — not because a roadmap needed filling.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Milestones -->
  <section class="section">
    <div class="container" style="max-width:720px;">
      <div class="section-head">
        <span class="eyebrow">Milestones</span>
        <h2>Where we've been, where we're headed</h2>
        <p>A short version of the roadmap — details subject to change as the product evolves.</p>
      </div>
      <div class="timeline" data-reveal>
        <div class="tl-item">
          <b>Foundation</b>
          <h4>Core workspace defined</h4>
          <p>Projects, tasks and attendance modules scoped around real day-to-day operating needs.</p>
        </div>
        <div class="tl-item">
          <b>Expansion</b>
          <h4>HR and client modules added</h4>
          <p>Leave, performance, tickets and client records connected to the same project data.</p>
        </div>
        <div class="tl-item">
          <b>Today</b>
          <h4>18 core modules, one workspace</h4>
          <p>Dashboards, exports and analytics layered on top for every department.</p>
        </div>
        <div class="tl-item">
          <b>Next</b>
          <h4>Deeper integrations and API access</h4>
          <p>Connecting Bitroxia PMS to the rest of your stack via webhooks and a public API.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Team -->
  <section class="section section-soft">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">Our Teams</span>
        <h2>The people behind Bitroxia PMS</h2>
        <p>Small, focused teams — each one owning a part of the workspace end to end.</p>
      </div>
      <div class="team-grid" data-reveal-group>
        <div class="team-card" data-reveal style="--i:0"><div class="team-avatar">PT</div><h4>Product Team</h4><span>Roadmap &amp; UX</span></div>
        <div class="team-card" data-reveal style="--i:1"><div class="team-avatar">EN</div><h4>Engineering Team</h4><span>Platform &amp; API</span></div>
        <div class="team-card" data-reveal style="--i:2"><div class="team-avatar">CS</div><h4>Customer Success</h4><span>Onboarding &amp; support</span></div>
        <div class="team-card" data-reveal style="--i:3"><div class="team-avatar">OP</div><h4>Operations Team</h4><span>Reliability &amp; security</span></div>
      </div>
    </div>
  </section>

  <!-- Careers -->
  <section class="section" id="careers">
    <div class="container">
      <div class="content-block" style="margin-inline:auto;text-align:center;max-width:600px;" data-reveal>
        <span class="eyebrow" style="justify-content:center;">Careers</span>
        <h2>Join the team behind Bitroxia PMS</h2>
        <p>We're always open to hearing from people who care about building calm, reliable software for operations teams. Reach out with what you'd want to work on.</p>
        <div style="margin-top:var(--sp-3)">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Get in Touch</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="section-tight">
    <div class="container">
      <div class="cta-banner" data-reveal>
        <h2>See Bitroxia PMS running</h2>
        <p>Request access and explore a workspace pre-loaded with sample projects, tickets and attendance data.</p>
        <div class="cta-banner-actions">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Request Access</a>
          <a href="{{ route('features') }}" class="btn btn-secondary" style="border-color:rgba(255,255,255,0.25);color:#fff;">Explore Features</a>
        </div>
        <p class="fine">Responsive web app · Secure login · Built for daily operations</p>
      </div>
    </div>
  </section>
</main>
@endsection
