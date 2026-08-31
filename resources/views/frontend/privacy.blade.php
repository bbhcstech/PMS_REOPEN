@extends('frontend.layouts-frontend.app')

@section('title', 'Privacy Policy — Bitroxia PMS')
@section('meta_description', 'How Bitroxia PMS collects, uses and protects your data.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Legal</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Privacy Policy</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Legal</span>
        <h1>Privacy Policy</h1>
        <p>How we collect, use and protect data across Bitroxia PMS.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="legal-body" data-reveal>
        <p class="legal-updated">Last updated: {{ date('F Y') }} · Data protection and governance standards.</p>
        <h2>1. Information We Collect</h2>
        <p>We collect account information (name, email, company), workspace data you enter (tasks, attendance, tickets, client records), and standard technical data (IP address, browser type) needed to operate the service securely.</p>
        <h2>2. How We Use Information</h2>
        <ul>
          <li>To provide and maintain the Bitroxia PMS workspace you configure</li>
          <li>To respond to support and contact requests submitted through the site</li>
          <li>To improve reliability, security and product features over time</li>
        </ul>
        <h2>3. Data Storage &amp; Security</h2>
        <p>Workspace data is stored per-tenant with role-based access controls. Production data is backed up on a regular schedule; access is limited to authorized personnel for support purposes only.</p>
        <h2>4. Sharing of Information</h2>
        <p>We do not sell workspace or contact data. Data may be shared with infrastructure providers strictly to operate the service, under confidentiality obligations.</p>
        <h2>5. Your Rights</h2>
        <p>You may request export or deletion of your workspace data by contacting <a href="mailto:info@bitroxia.com" style="color:var(--accent-2)">info@bitroxia.com</a>.</p>
        <h2>6. Contact</h2>
        <p>Questions about this policy can be sent to <a href="mailto:info@bitroxia.com" style="color:var(--accent-2)">info@bitroxia.com</a>.</p>
      </div>
    </div>
  </section>
</main>
@endsection
