@extends('frontend.layouts-frontend.app')

@section('title', 'Solutions — Bitroxia PMS')
@section('meta_description', 'How Bitroxia PMS fits enterprises, startups, HR teams, remote teams and developers.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Solutions</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Solutions</span>
        <h1>Built for how your team is actually structured</h1>
        <p>Whether you're a five-person startup or a multi-department enterprise, Bitroxia PMS adapts its roles, dashboards and reporting to the way your organization really works.</p>
        <div class="page-hero-actions">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Talk to Us</a>
        </div>
      </div>
    </div>
  </section>

  <div class="jump-nav">
    <div class="container">
      <div class="jump-row">
        <a href="#enterprise">Enterprises</a>
        <a href="#startups">Startups</a>
        <a href="#hr">HR Teams</a>
        <a href="#remote">Remote Teams</a>
        <a href="#developers">Developers</a>
      </div>
    </div>
  </div>

  <!-- 1. Enterprises -->
  <section class="module-block" id="enterprise">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/></svg>
          </div>
          <span class="eyebrow">Business Teams</span>
          <h2>For Enterprises</h2>
          <p>Scale governance across departments without losing a single-source-of-truth view. Role-based access, audit logs and contract tracking keep larger orgs accountable.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Granular roles: admin, employee, client, superadmin</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Full audit log across every module</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Multi-department reporting rollups</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Contract and vendor record tracking</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>Engineering dept.</b>142 members · 18 projects</span><span class="ltag">Active</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)"></span><span class="ltext"><b>Client Success dept.</b>36 members · 240 tickets</span><span class="ltag">Active</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)"></span><span class="ltext"><b>Finance dept.</b>12 members · Payroll sync</span><span class="ltag">Active</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. Startups -->
  <section class="module-block reverse" id="startups">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7z"/></svg>
          </div>
          <span class="eyebrow">Business Teams</span>
          <h2>For Startups</h2>
          <p>Move fast with one workspace instead of stitching together five tools. Set up projects, invite your team and start tracking attendance the same afternoon.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Guided setup in under a day</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Flat pricing with every module included</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>No per-feature paywalls between teams</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Scales with you — no re-platforming later</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-kanban">
              <div class="kcol"><span class="klabel">To Do</span><div class="kcard">MVP scope doc</div><div class="kcard">Landing page copy</div></div>
              <div class="kcol"><span class="klabel">In Progress</span><div class="kcard">Auth flow</div><div class="kcard">Pricing page</div></div>
              <div class="kcol"><span class="klabel">Done</span><div class="kcard">Logo &amp; brand kit</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. HR Teams -->
  <section class="module-block" id="hr">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="3"/><path d="M2 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 3.1a4 4 0 010 7.8M22 20c0-2.8-2-5.2-4.7-6.4"/></svg>
          </div>
          <span class="eyebrow">Operations</span>
          <h2>For HR Teams</h2>
          <p>People, attendance and leave sit next to project data — so capacity planning uses real numbers instead of a manager's best guess.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Attendance and leave in one policy engine</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Department, designation and holiday setup</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Performance review cycles built in</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Exportable payroll-adjacent reports</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-calendar">
              <span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span><span class="on"></span>
              <span class="on"></span><span class=""></span><span class="on"></span><span class="on"></span><span class=""></span>
              <span class="off"></span><span class="on"></span><span class="on"></span><span class=""></span><span class="on"></span>
              <span class="on"></span><span class=""></span><span class="off"></span><span class="on"></span><span class="on"></span><span class=""></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Remote Teams -->
  <section class="module-block reverse" id="remote">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #34D399, #6EE7B7)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
          </div>
          <span class="eyebrow">Operations</span>
          <h2>For Remote Teams</h2>
          <p>Coordinate distributed work across time zones with location-aware attendance, async-friendly tickets, and dashboards that stay legible on any device.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Location-based clock-ins for field/remote staff</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Async ticket threads with full context</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Fully responsive app for any screen size</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Availability visible across time zones</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>Priya Sharma</b>Kolkata · Online</span><span class="ltag">Active</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)"></span><span class="ltext"><b>Arjun Mehta</b>Bengaluru · Online</span><span class="ltag">Active</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)"></span><span class="ltext"><b>Sofia Almeida</b>Lisbon · Offline</span><span class="ltag">Away</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. Developers -->
  <section class="module-block" id="developers">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #F59E0B, #FBBF24)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          </div>
          <span class="eyebrow">Delivery</span>
          <h2>For Developers</h2>
          <p>Tasks, boards and delivery flow built around how engineering teams actually ship — with an API to connect Bitroxia PMS to the rest of your stack.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Kanban boards tuned for sprint workflows</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>API access for custom integrations</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Webhooks on task and ticket status change</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Exportable activity logs for audits</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-gantt">
              <div class="gantt-row"><span class="label">Sprint 12</span><div class="track"><i style="left:0%;width:24%"></i></div></div>
              <div class="gantt-row"><span class="label">Sprint 13</span><div class="track"><i style="left:22%;width:24%"></i></div></div>
              <div class="gantt-row"><span class="label">Sprint 14</span><div class="track"><i style="left:44%;width:24%"></i></div></div>
              <div class="gantt-row"><span class="label">Release</span><div class="track"><i style="left:70%;width:16%"></i></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="section-tight">
    <div class="container">
      <div class="cta-banner" data-reveal>
        <h2>Not sure which setup fits your team?</h2>
        <p>Tell us your team size and structure — we'll suggest the right roles, departments and modules to start with.</p>
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
