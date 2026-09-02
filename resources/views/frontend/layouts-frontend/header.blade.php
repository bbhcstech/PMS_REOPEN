<header class="nav" id="siteNav">
  <div class="container">
    <a href="{{ route('home') }}" class="brand">
      <img src="{{ asset('logo.png') }}" alt="Bitroxia logo">
      <span>Bitroxia<small>Project &amp; HR Workspace</small></span>
    </a>

    <nav aria-label="Primary">
      <ul class="nav-links">
        <li class="{{ request()->is('product*') ? 'is-active' : '' }}">
          <button class="nav-top-link" aria-expanded="false">Product
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Project Management</div>
              <a href="{{ route('features') }}#tasks"><strong>Task Management</strong><span>Organize and track tasks</span></a>
              <a href="{{ route('features') }}#gantt"><strong>Gantt Charts</strong><span>Visual project timelines</span></a>
              <a href="{{ route('features') }}#kanban"><strong>Kanban Boards</strong><span>Agile workflow</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">HR Management</div>
              <a href="{{ route('features') }}#attendance"><strong>Attendance</strong><span>Track employee hours</span></a>
              <a href="{{ route('features') }}#leave"><strong>Leave Management</strong><span>Manage time off</span></a>
              <a href="{{ route('features') }}#performance"><strong>Performance</strong><span>Employee reviews</span></a>
            </div>
          </div>
        </li>
        <li class="{{ request()->is('solutions*') ? 'is-active' : '' }}">
          <button class="nav-top-link" aria-expanded="false">Solutions
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Business Teams</div>
              <a href="{{ route('solutions') }}#enterprise"><strong>For Enterprises</strong><span>Scale governance and teams</span></a>
              <a href="{{ route('solutions') }}#startups"><strong>For Startups</strong><span>Move fast with one workspace</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Operations</div>
              <a href="{{ route('solutions') }}#hr"><strong>For HR Teams</strong><span>People, attendance and leave</span></a>
              <a href="{{ route('solutions') }}#remote"><strong>For Remote Teams</strong><span>Coordinate distributed work</span></a>
              <a href="{{ route('solutions') }}#developers"><strong>For Developers</strong><span>Visibility and accountability</span></a>
            </div>
          </div>
        </li>
        <li><a href="{{ route('features') }}"{!! request()->routeIs('features') ? ' aria-current="page"' : '' !!}>Features</a></li>
        <li><a href="{{ route('pricing') }}"{!! request()->routeIs('pricing') ? ' aria-current="page"' : '' !!}>Pricing</a></li>
        <li class="{{ request()->is('resources*') ? 'is-active' : '' }}">
          <button class="nav-top-link" aria-expanded="false">Resources
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Learn</div>
              <a href="{{ route('resources') }}#blog"><strong>Blog</strong><span>Product and workflow updates</span></a>
              <a href="{{ route('resources') }}#faq"><strong>FAQ</strong><span>Common questions answered</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Support</div>
              <a href="{{ route('resources') }}#docs"><strong>Documentation</strong><span>Guides and setup help</span></a>
              <a href="{{ route('resources') }}#help"><strong>Help Center</strong><span>Find help for your team</span></a>
              <a href="{{ route('resources') }}#api"><strong>API</strong><span>Connect external systems</span></a>
            </div>
          </div>
        </li>
        <li class="{{ request()->is('company*') || request()->routeIs('about') || request()->routeIs('contact') ? 'is-active' : '' }}">
          <button class="nav-top-link" aria-expanded="false">Company
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Bitroxia</div>
              <a href="{{ route('company.about') }}"><strong>About Us</strong><span>Who we are and what we build</span></a>
              <a href="{{ route('company.about') }}#careers"><strong>Careers</strong><span>Join the team behind PMS</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Legal &amp; Contact</div>
              <a href="{{ route('company.contact') }}"><strong>Contact</strong><span>Talk to us about your needs</span></a>
              <a href="{{ route('company.privacy') }}"><strong>Privacy Policy</strong><span>How data is handled</span></a>
              <a href="{{ route('company.terms') }}"><strong>Terms</strong><span>Usage terms and policies</span></a>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <div class="nav-actions">
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle light and dark theme" aria-pressed="false">
        <span class="knob">
          <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
          <svg class="icon-moon" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
        </span>
      </button>
      <a href="{{ route('login') }}" class="nav-login">Login</a>
      <a href="{{ route('company.contact') }}" class="btn btn-primary btn-sm">Request Access</a>
      <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileDrawer">
        <span></span>
      </button>
    </div>
  </div>

  <!-- Mobile drawer -->
  <div class="mobile-drawer" id="mobileDrawer">
    <div class="scrim" data-close-drawer></div>
    <div class="mobile-panel">
      <ul class="mobile-links">
        <li>
          <details>
            <summary>Product <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{{ route('features') }}#tasks" data-close-drawer>Task Management</a>
              <a href="{{ route('features') }}#gantt" data-close-drawer>Gantt Charts</a>
              <a href="{{ route('features') }}#kanban" data-close-drawer>Kanban Boards</a>
              <a href="{{ route('features') }}#attendance" data-close-drawer>Attendance</a>
              <a href="{{ route('features') }}#leave" data-close-drawer>Leave Management</a>
              <a href="{{ route('features') }}#performance" data-close-drawer>Performance</a>
            </div>
          </details>
        </li>
        <li>
          <details>
            <summary>Solutions <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{{ route('solutions') }}#enterprise" data-close-drawer>For Enterprises</a>
              <a href="{{ route('solutions') }}#startups" data-close-drawer>For Startups</a>
              <a href="{{ route('solutions') }}#hr" data-close-drawer>For HR Teams</a>
              <a href="{{ route('solutions') }}#remote" data-close-drawer>For Remote Teams</a>
              <a href="{{ route('solutions') }}#developers" data-close-drawer>For Developers</a>
            </div>
          </details>
        </li>
        <li><a href="{{ route('features') }}" data-close-drawer>Features</a></li>
        <li><a href="{{ route('pricing') }}" data-close-drawer>Pricing</a></li>
        <li>
          <details>
            <summary>Resources <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{{ route('resources') }}#blog" data-close-drawer>Blog</a>
              <a href="{{ route('resources') }}#faq" data-close-drawer>FAQ</a>
              <a href="{{ route('resources') }}#docs" data-close-drawer>Documentation</a>
              <a href="{{ route('resources') }}#help" data-close-drawer>Help Center</a>
              <a href="{{ route('resources') }}#api" data-close-drawer>API</a>
            </div>
          </details>
        </li>
        <li>
          <details>
            <summary>Company <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{{ route('company.about') }}" data-close-drawer>About Us</a>
              <a href="{{ route('company.about') }}#careers" data-close-drawer>Careers</a>
              <a href="{{ route('company.contact') }}" data-close-drawer>Contact</a>
              <a href="{{ route('company.privacy') }}" data-close-drawer>Privacy Policy</a>
              <a href="{{ route('company.terms') }}" data-close-drawer>Terms</a>
            </div>
          </details>
        </li>
      </ul>
      <div class="mobile-cta">
        <a href="{{ route('login') }}" class="btn btn-secondary btn-block">Login</a>
        <a href="{{ route('company.contact') }}" class="btn btn-primary btn-block" data-close-drawer>Request Access</a>
      </div>
    </div>
  </div>
</header>
