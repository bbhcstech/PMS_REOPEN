# -*- coding: utf-8 -*-

def admin_login_html():
    return """<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Bitroxia PMS</title>
<meta name="description" content="Staff login for the Bitroxia PMS admin dashboard.">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#2F6BFF">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="../assets/img/logo.png">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
  <div class="admin-login">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="admin-login-card">
      <div class="admin-login-brand">
        <img src="../assets/img/logo.png" alt="Bitroxia logo">
        <span>Bitroxia</span>
      </div>
      <h1>Admin Sign In</h1>
      <p>Requests submitted from the Contact page land in this dashboard.</p>

      <div class="admin-error" id="adminError">Incorrect email or password. Please try again.</div>

      <form id="adminLoginForm">
        <div class="field">
          <label for="adminEmail">Email</label>
          <input id="adminEmail" type="email" placeholder="admin@bitroxia.com" required autocomplete="username">
        </div>
        <div class="field">
          <label for="adminPass">Password</label>
          <input id="adminPass" type="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
      </form>

      <div class="admin-demo-note">
        <b>Demo credentials</b> &middot; This prototype checks credentials in the browser only.<br>
        Email: <code>admin@bitroxia.com</code><br>
        Password: <code>bitroxia@2026</code><br><br>
        Wire this to real authentication against your backend before launch — see README.md.
      </div>
    </div>
  </div>
  <script src="../assets/js/leads.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>
</html>
"""

def admin_dashboard_html():
    return """<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Requests — Bitroxia PMS Admin</title>
<meta name="description" content="Admin dashboard for Bitroxia PMS contact requests.">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#2F6BFF">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="../assets/img/logo.png">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
  <div class="admin-shell" id="adminDashboard">
    <aside class="admin-sidebar" id="adminSidebar">
      <a href="../index.html" class="brand"><img src="../assets/img/logo.png" alt="Bitroxia logo"><span>Bitroxia<small>Admin</small></span></a>
      <nav class="admin-nav">
        <a href="dashboard.html" class="is-active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          Requests <span class="count" id="navCountNew">0</span>
        </a>
        <a href="../index.html">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
          View Public Site
        </a>
        <a href="../features.html">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          Modules
        </a>
      </nav>
      <div class="admin-sidebar-foot">
        <div class="who"><b>Admin</b><span>admin@bitroxia.com</span></div>
        <button class="admin-logout" id="adminLogout" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg></button>
      </div>
    </aside>

    <main class="admin-main">
      <div class="admin-topbar">
        <div>
          <button class="hamburger" id="adminSidebarToggle" aria-label="Toggle sidebar"><span></span></button>
          <h1>Contact Requests</h1>
          <p>Submissions from the public Contact page appear here automatically.</p>
        </div>
      </div>

      <div class="admin-content">
        <div class="admin-stats">
          <div class="admin-stat-card blue"><span>Total Requests</span><b id="statTotal">0</b></div>
          <div class="admin-stat-card purple"><span>New</span><b id="statNew">0</b></div>
          <div class="admin-stat-card cyan"><span>Read</span><b id="statRead">0</b></div>
          <div class="admin-stat-card green"><span>Last 7 Days</span><b id="statWeek">0</b></div>
        </div>

        <div class="admin-toolbar">
          <div class="admin-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" id="leadsSearch" placeholder="Search name, email, company&hellip;">
          </div>
          <div class="admin-filters">
            <button data-filter="all" class="is-active">All</button>
            <button data-filter="new">New</button>
            <button data-filter="read">Read</button>
          </div>
        </div>

        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Contact</th><th>Company</th><th>Team Size</th><th>Source</th><th>Received</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="leadsTableBody"></tbody>
          </table>
          <div class="admin-empty" id="leadsEmpty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <p>No requests yet. Submit the Contact form on the public site to see it appear here.</p>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div class="admin-modal-scrim" id="leadModal">
    <div class="admin-modal">
      <div class="admin-modal-head">
        <h3>Request Details</h3>
        <button id="leadModalClose" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
      </div>
      <div class="admin-modal-field"><label>Name</label><div id="modalName"></div></div>
      <div class="admin-modal-field"><label>Email</label><div id="modalEmail"></div></div>
      <div class="admin-modal-field"><label>Company</label><div id="modalCompany"></div></div>
      <div class="admin-modal-field"><label>Team Size</label><div id="modalTeamSize"></div></div>
      <div class="admin-modal-field"><label>Source Page</label><div id="modalSource"></div></div>
      <div class="admin-modal-field"><label>Received</label><div id="modalDate"></div></div>
      <div class="admin-modal-field">
        <label>Message</label>
        <div class="admin-modal-msg" id="modalMessage"></div>
      </div>
      <div class="admin-modal-actions">
        <button class="btn btn-secondary btn-block" id="modalMarkRead">Mark as Read</button>
        <button class="btn btn-primary btn-block" id="modalDelete" style="background:linear-gradient(135deg,#FB7185,#F43F5E)">Delete</button>
      </div>
    </div>
  </div>

  <script src="../assets/js/leads.js"></script>
  <script src="../assets/js/admin.js"></script>
</body>
</html>
"""
