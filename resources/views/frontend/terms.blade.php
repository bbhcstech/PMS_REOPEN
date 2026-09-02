@extends('frontend.layouts-frontend.app')

@section('title', ($termsTitle ?? 'Terms of Service') . ' — Bitroxia PMS')
@section('meta_description', 'The terms that govern use of Bitroxia PMS.')

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
        <span>{{ $termsTitle ?? 'Terms' }}</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Legal</span>
        <h1>{{ $termsTitle ?? 'Terms of Service' }}</h1>
        <p>The terms that govern use of the Bitroxia PMS platform and services.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="legal-body" data-reveal>
        @if(!empty($effectiveDate))
          <p class="legal-updated">Effective date: {{ \Carbon\Carbon::parse($effectiveDate)->format('d M Y') }}</p>
        @else
          <p class="legal-updated">Last updated: {{ date('F Y') }} · Usage terms and policies.</p>
        @endif

        @if(!empty($termsContent))
          <div class="custom-legal-content">
            {!! nl2br(e($termsContent)) !!}
          </div>
        @else
          <h2>1. Acceptance of Terms</h2>
          <p>By accessing or using Bitroxia PMS, your organization agrees to these terms on behalf of its authorized users.</p>
          <h2>2. Use of the Service</h2>
          <ul>
            <li>You are responsible for the accuracy of data your team enters</li>
            <li>Accounts are provisioned per workspace with role-based access</li>
            <li>Misuse, including unauthorized access attempts, may result in suspension</li>
          </ul>
          <h2>3. Subscription &amp; Billing</h2>
          <p>Plans are billed per the tier selected at signup. Upgrades, downgrades and cancellations are handled per the billing terms confirmed at the time of purchase.</p>
          <h2>4. Data Ownership</h2>
          <p>Your organization retains ownership of all data entered into your workspace. Export options are available on request.</p>
          <h2>5. Limitation of Liability</h2>
          <p>Bitroxia PMS is provided "as is" without warranties beyond those explicitly stated in a signed service agreement.</p>
          <h2>6. Changes to Terms</h2>
          <p>We may update these terms from time to time; continued use after changes constitutes acceptance of the revised terms.</p>
        @endif
      </div>
    </div>
  </section>
</main>
@endsection
