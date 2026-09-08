<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col footer-brand">
        <a href="{{ route('home') }}" class="brand">
          <img src="{{ asset('logo.png') }}" alt="Bitroxia logo">
          <span>Bitroxia</span>
        </a>
        <p>A practical project, HR, attendance and reporting workspace for teams that need clear ownership and faster delivery.</p>
        <div class="footer-social">
          <a href="#" aria-label="Bitroxia on LinkedIn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4V8h4v1.5A5.99 5.99 0 0116 8z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
          <a href="#" aria-label="Bitroxia on X"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 4L6 20M6 4l12 16"/></svg></a>
          <a href="#" aria-label="Bitroxia on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
        </div>
      </div>
      <div class="footer-col">
        <h5>Product</h5>
        <ul>
          <li><a href="{{ route('features') }}#tasks">Task Management</a></li>
          <li><a href="{{ route('features') }}#gantt">Gantt Charts</a></li>
          <li><a href="{{ route('features') }}#kanban">Kanban Boards</a></li>
          <li><a href="{{ route('features') }}#attendance">Attendance</a></li>
          <li><a href="{{ route('features') }}#leave">Leave Management</a></li>
          <li><a href="{{ route('features') }}#dashboard">Dashboard</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Company</h5>
        <ul>
          <li><a href="{{ route('company.about') }}">About Us</a></li>
          <li><a href="{{ route('features') }}">Features</a></li>
          <li><a href="{{ route('pricing') }}">Pricing</a></li>
          <li><a href="{{ route('company.about') }}#careers">Careers</a></li>
          <li><a href="{{ route('company.contact') }}">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Resources</h5>
        <ul>
          <li><a href="{{ route('resources') }}#faq">FAQ</a></li>
          <li><a href="{{ route('resources') }}#blog">Blog</a></li>
          <li><a href="{{ route('resources') }}#docs">Documentation</a></li>
          <li><a href="{{ route('resources') }}#help">Help Center</a></li>
          <li><a href="{{ route('resources') }}#api">API &amp; Webhooks</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Contact</h5>
        <ul>
          <li><a href="mailto:info@bitroxia.com">info@bitroxia.com</a></li>
          <li><a href="tel:+910000000000">+91 00000 00000</a></li>
          <li>India &amp; global remote delivery</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; {{ date('Y') }} Bitroxia PMS. All rights reserved.</p>
      <div class="footer-legal">
        <a href="{{ route('company.privacy') }}">Privacy Policy</a>
        <a href="{{ route('company.terms') }}">Terms of Service</a>
        <a href="{{ route('login') }}">Staff Login</a>
      </div>
    </div>
  </div>
</footer>
