@extends('frontend.layouts-frontend.app')

@section('title', 'Contact — Bitroxia PMS')
@section('meta_description', 'Get in touch with Bitroxia PMS for project management, HR, attendance, ticketing or reporting needs.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Contact</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Contact</span>
        <h1>Talk to us about your PMS workflow</h1>
        <p>Need project management, attendance, HR, ticket, client, or reporting customization? Share your requirement and we'll help align modules, roles and reports.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="contact-grid">
        <div class="contact-info" data-reveal>
          <div class="contact-detail">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </span>
            <div>
              <b>Email</b>
              <a href="mailto:info@bitroxia.com">info@bitroxia.com</a>
            </div>
          </div>
          <div class="contact-detail">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            </span>
            <div>
              <b>Phone</b>
              <a href="tel:+910000000000">+91 00000 00000</a>
            </div>
          </div>
          <div class="contact-detail">
            <span class="ic">
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg>
            </span>
            <div>
              <b>Delivery</b>
              <span>India and global remote delivery</span>
            </div>
          </div>
          <div class="map-embed">
            <iframe src="https://www.google.com/maps?q=India&output=embed" loading="lazy" title="Business location map" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>

        <form class="contact-form" id="contactFormFull" method="POST" action="{{ route('company.contact.submit') }}" data-reveal>
          @csrf
          <input type="hidden" name="source" value="Contact Page Inquiry">

          <div class="form-alert {{ session('success') ? 'is-success is-visible' : '' }}" id="contactAlertFull">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <span>{{ session('success') ?? '' }}</span>
          </div>

          <div class="form-row">
            <div class="field">
              <label for="fname2">Full name</label>
              <input id="fname2" name="name" data-field="name" type="text" placeholder="Your name" required value="{{ old('name') }}">
            </div>
            <div class="field">
              <label for="femail2">Work email</label>
              <input id="femail2" name="email" data-field="email" type="email" placeholder="you@company.com" required value="{{ old('email') }}">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label for="fcompany2">Company</label>
              <input id="fcompany2" name="company" data-field="company" type="text" placeholder="Company name" value="{{ old('company') }}">
            </div>
            <div class="field">
              <label for="fsize2">Team size</label>
              <select id="fsize2" name="team_size" data-field="teamSize">
                <option value="1–15">1–15</option>
                <option value="16–50">16–50</option>
                <option value="51–100">51–100</option>
                <option value="100+">100+</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="fmsg2">What do you need?</label>
            <textarea id="fmsg2" name="message" data-field="message" placeholder="Tell us about your project management, HR, ticketing, or reporting needs">{{ old('message') }}</textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Send Message</button>
        </form>
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  var form = document.getElementById('contactFormFull');
  var alertEl = document.getElementById('contactAlertFull');
  if (form && window.BitroxiaLeads) {
    window.BitroxiaLeads.wireForm(form, alertEl, 'Contact Page');
  }
});
</script>
@endpush
