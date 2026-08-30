@extends('frontend.layouts-frontend.app')

@section('title', 'Resources — Bitroxia PMS')
@section('meta_description', 'Blog, FAQ, documentation, help center and API reference for Bitroxia PMS.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Resources</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Resources</span>
        <h1>Guides, updates and answers</h1>
        <p>Everything you need to get a team running on Bitroxia PMS — product updates, setup guides, support channels and API documentation.</p>
      </div>
    </div>
  </section>

  <div class="jump-nav">
    <div class="container">
      <div class="jump-row">
        <a href="#blog">Blog</a>
        <a href="#faq">FAQ</a>
        <a href="#docs">Docs</a>
        <a href="#help">Help Center</a>
        <a href="#api">API</a>
      </div>
    </div>
  </div>

  <!-- Blog -->
  <section class="section" id="blog">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">From the Blog</span>
        <h2>Product and workflow updates</h2>
        <p>Notes on running project, HR and reporting workflows well — from the team building Bitroxia PMS.</p>
      </div>
      <div class="blog-grid" data-reveal-group>
        <article class="blog-card" data-reveal style="--i:0">
          <div class="blog-thumb">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5M3 16.5l9 5 9-5"/></svg>
          </div>
          <div class="blog-body">
            <div class="blog-meta"><span>Product</span><span>&middot;</span><span>6 min read</span></div>
            <h3>5 Signs Your Team Has Outgrown Spreadsheets</h3>
            <p>How to spot the moment when tracking tasks and attendance in spreadsheets starts costing more time than it saves.</p>
            <a href="{{ route('company.contact') }}" class="feature-link">Read Article <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </article>
        <article class="blog-card" data-reveal style="--i:1">
          <div class="blog-thumb">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          </div>
          <div class="blog-body">
            <div class="blog-meta"><span>Workflow</span><span>&middot;</span><span>5 min read</span></div>
            <h3>Structuring Approval Workflows Without Slowing Teams Down</h3>
            <p>A practical approach to leave, expense and contract approvals that protects capacity without adding bottlenecks.</p>
            <a href="{{ route('company.contact') }}" class="feature-link">Read Article <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </article>
        <article class="blog-card" data-reveal style="--i:2">
          <div class="blog-thumb">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          </div>
          <div class="blog-body">
            <div class="blog-meta"><span>HR</span><span>&middot;</span><span>7 min read</span></div>
            <h3>An Attendance Checklist for Distributed Teams</h3>
            <p>What to configure before rolling out location-based clock-ins across a remote or hybrid workforce.</p>
            <a href="{{ route('company.contact') }}" class="feature-link">Read Article <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section section-soft" id="faq">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">FAQ</span>
        <h2>Common questions answered</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item is-open">
          <button class="faq-q" type="button"><span>How do I invite my team?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">From the admin workspace, add employees by email under HR &gt; Employees. Each invite includes a role (admin, employee, client) that controls what they can see.</div></div>
        </div>
        <div class="faq-item">
          <button class="faq-q" type="button"><span>Can I import existing tasks from another tool?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">Yes — CSV import is supported for tasks, employees and client records during onboarding. Our team can help map your existing fields.</div></div>
        </div>
        <div class="faq-item">
          <button class="faq-q" type="button"><span>Is my data backed up?</span><span class="plus"></span></button>
          <div class="faq-a"><div class="faq-a-inner">Production workspaces run on daily automated backups with point-in-time recovery available on Growth and Enterprise plans.</div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Docs -->
  <section class="section" id="docs">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">Documentation</span>
        <h2>Guides and setup help</h2>
      </div>
      <div class="util-grid">
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7z"/></svg>
          </div>
          <h3>Getting Started Guide</h3>
          <p>Set up departments, roles and your first project in under an hour.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">Read Guide <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5M3 16.5l9 5 9-5"/></svg>
          </div>
          <h3>Import &amp; Migration</h3>
          <p>Bring over tasks, employees and client records from your current tools.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">Read Guide <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/></svg>
          </div>
          <h3>Roles &amp; Permissions</h3>
          <p>Configure admin, employee and client access scoped to what each role needs.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">Read Guide <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </div>
  </section>

  <!-- Help Center -->
  <section class="section section-soft" id="help">
    <div class="container">
      <div class="section-head center">
        <span class="eyebrow">Help Center</span>
        <h2>Find help for your team</h2>
      </div>
      <div class="util-grid">
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, #34D399, #6EE7B7)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="M5 5l4.5 4.5M19 5l-4.5 4.5M5 19l4.5-4.5M19 19l-4.5-4.5"/></svg>
          </div>
          <h3>Contact Support</h3>
          <p>Reach our team directly for setup help or account issues.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">Contact Us <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, #F59E0B, #FBBF24)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l3-4 3 3 5-7"/></svg>
          </div>
          <h3>System Status</h3>
          <p>Check current uptime and any ongoing maintenance.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">View Status <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, #FB7185, #FCA5A5)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="3"/><path d="M2 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M16 3.1a4 4 0 010 7.8M22 20c0-2.8-2-5.2-4.7-6.4"/></svg>
          </div>
          <h3>Community</h3>
          <p>Ask questions and share setup tips with other Bitroxia teams.</p>
          <a href="{{ route('company.contact') }}" class="feature-link">Join Community <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
      </div>
    </div>
  </section>

  <!-- API -->
  <section class="section" id="api">
    <div class="container">
      <div class="content-block" style="margin-inline:auto;text-align:center;max-width:640px;" data-reveal>
        <span class="eyebrow" style="justify-content:center;">Developers</span>
        <h2>Connect external systems</h2>
        <p>The Bitroxia PMS API lets you read and write tasks, attendance, tickets and client records from your own tools.</p>
      </div>
      <div class="util-grid">
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          </div>
          <h3>Authentication</h3>
          <p>Token-based API access scoped per workspace and role.</p>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2v4M15 2v4M6 9h12l-1 5a5 5 0 01-10 0z"/><path d="M12 16v6"/></svg>
          </div>
          <h3>Webhooks</h3>
          <p>Subscribe to task, ticket and attendance status changes in real time.</p>
        </div>
        <div class="util-card">
          <div class="feature-icon" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5A2.5 2.5 0 016.5 2H20v16.5A2.5 2.5 0 0117.5 21H4z"/><path d="M4 4.5A2.5 2.5 0 006.5 22H20"/></svg>
          </div>
          <h3>Rate Limits &amp; Docs</h3>
          <p>Full endpoint reference with request and response examples.</p>
        </div>
      </div>
      <div style="text-align:center;margin-top:var(--sp-4);">
        <a href="{{ route('company.contact') }}" class="btn btn-primary">Request API Access</a>
      </div>
    </div>
  </section>
</main>
@endsection
