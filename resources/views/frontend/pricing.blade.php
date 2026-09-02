@extends('frontend.layouts-frontend.app')

@section('title', 'Pricing — Bitroxia PMS')
@section('meta_description', 'Clear, transparent pricing for Bitroxia PMS. Choose between Starter, Growth and Enterprise plans.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Pricing</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Simple Pricing</span>
        <h1>One plan, every module included</h1>
        <p>No feature paywalls between departments — projects, HR, tickets and reporting ship together from day one. Scale smoothly as your organization grows.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="dept-grid" data-reveal-group>
        <div class="dept-card" data-reveal style="--i:0">
          <span class="tag">Starter</span>
          <h3>&#8377;499<span style="font-size:14px;color:var(--ink-faint);font-weight:500;"> /user/mo</span></h3>
          <p>For small, focused teams running their first projects.</p>
          <ul>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Up to 15 team members</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Tasks, Kanban &amp; attendance</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Standard reporting</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Email support</li>
          </ul>
          <div style="margin-top:var(--sp-3)"><a href="{{ route('company.contact') }}" class="btn btn-secondary btn-block">Talk to sales</a></div>
        </div>
        <div class="dept-card" style="border-color:var(--brand-blue)" data-reveal style="--i:1">
          <span class="tag">Growth</span>
          <h3>&#8377;899<span style="font-size:14px;color:var(--ink-faint);font-weight:500;"> /user/mo</span></h3>
          <p>For scaling departments adding HR and client workflows.</p>
          <ul>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Up to 100 team members</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Leave, tickets &amp; clients</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Custom dashboards</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Priority support</li>
          </ul>
          <div style="margin-top:var(--sp-3)"><a href="{{ route('company.contact') }}" class="btn btn-primary btn-block">Talk to sales</a></div>
        </div>
        <div class="dept-card" data-reveal style="--i:2">
          <span class="tag">Enterprise</span>
          <h3>Custom</h3>
          <p>For multi-department orgs needing governance at scale.</p>
          <ul>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Unlimited team members</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Contracts &amp; audit logs</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Dedicated onboarding</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>SLA-backed support</li>
          </ul>
          <div style="margin-top:var(--sp-3)"><a href="{{ route('company.contact') }}" class="btn btn-secondary btn-block">Talk to sales</a></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Compare Table -->
  <section class="section section-soft">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">Compare Plans</span>
        <h2>What's included at each tier</h2>
        <p>Every module ships in every plan — tiers differ mainly by team size, governance depth and support level.</p>
      </div>
      <div class="compare-table-wrap">
        <table class="compare-table">
          <thead>
            <tr>
              <th>Feature</th>
              <th>Starter</th>
              <th>Growth</th>
              <th>Enterprise</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Tasks &amp; Kanban boards</td><td class="yes">Included</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Gantt charts</td><td class="yes">Included</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Attendance &amp; timelogs</td><td class="yes">Included</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Leave &amp; holiday management</td><td class="no">—</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Tickets &amp; client records</td><td class="no">—</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Custom dashboards</td><td class="no">—</td><td class="yes">Included</td><td class="yes">Included</td></tr>
            <tr><td>Contracts &amp; audit logs</td><td class="no">—</td><td class="no">—</td><td class="yes">Included</td></tr>
            <tr><td>Dedicated onboarding</td><td class="no">—</td><td class="no">—</td><td class="yes">Included</td></tr>
            <tr><td>Support</td><td>Email</td><td>Priority</td><td>SLA-backed</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section" id="faq">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">Billing FAQ</span>
        <h2>Common pricing questions</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item is-open">
          <button class="faq-q" type="button"><span>Can I switch plans later?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">Yes — you can move between Starter, Growth and Enterprise at any time. Your project, task and attendance history carries over automatically.</div></div>
        </div>
        <div class="faq-item">
          <button class="faq-q" type="button"><span>Is there a free trial?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">Request access and we'll set up a preview workspace with sample data so your team can evaluate the modules before committing to a plan.</div></div>
        </div>
        <div class="faq-item">
          <button class="faq-q" type="button"><span>Do you offer annual billing?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">Yes, annual billing is available on all plans at a discount versus monthly. Ask your sales contact for current annual rates.</div></div>
        </div>
        <div class="faq-item">
          <button class="faq-q" type="button"><span>What happens if I go over my team size limit?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">We'll reach out before any limit is enforced so you can upgrade smoothly — no workspace is ever locked without notice.</div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="section-tight">
    <div class="container">
      <div class="cta-banner" data-reveal>
        <h2>Get exact pricing for your team</h2>
        <p>Share your team size and modules you need — we'll send a tailored quote within one business day.</p>
        <div class="cta-banner-actions">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Get a Quote</a>
          <a href="{{ route('features') }}" class="btn btn-secondary" style="border-color:rgba(255,255,255,0.25);color:#fff;">Explore Features</a>
        </div>
        <p class="fine">Responsive web app · Secure login · Built for daily operations</p>
      </div>
    </div>
  </section>
</main>
@endsection
