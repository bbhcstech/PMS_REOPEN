#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generates every inner page of the Bitroxia PMS site from shared
nav/footer templates + per-page content blocks defined in
content_*.py siblings. Run: python3 build.py
"""
import os

ROOT = os.path.dirname(os.path.abspath(__file__))

def HEAD(title, desc, prefix, extra_css=""):
    return f"""<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{title}</title>
<meta name="description" content="{desc}">
<meta name="theme-color" content="#2F6BFF">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{prefix}assets/img/logo.png">
<link rel="stylesheet" href="{prefix}assets/css/base.css">
<link rel="stylesheet" href="{prefix}assets/css/nav-hero.css">
<link rel="stylesheet" href="{prefix}assets/css/sections.css">
<link rel="stylesheet" href="{prefix}assets/css/footer-misc.css">
<link rel="stylesheet" href="{prefix}assets/css/pages.css">
<link rel="stylesheet" href="{prefix}assets/css/responsive.css">
{extra_css}"""

def NAV(prefix, active=""):
    p = prefix
    def cur(key):
        return ' aria-current="page"' if key == active else ""
    return f"""<header class="nav" id="siteNav">
  <div class="container">
    <a href="{p}index.html" class="brand">
      <img src="{p}assets/img/logo.png" alt="Bitroxia logo">
      <span>Bitroxia<small>Project &amp; HR Workspace</small></span>
    </a>

    <nav aria-label="Primary">
      <ul class="nav-links">
        <li>
          <button class="nav-top-link" aria-expanded="false">Product
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Project Management</div>
              <a href="{p}features.html#tasks"><strong>Task Management</strong><span>Organize and track tasks</span></a>
              <a href="{p}features.html#gantt"><strong>Gantt Charts</strong><span>Visual project timelines</span></a>
              <a href="{p}features.html#kanban"><strong>Kanban Boards</strong><span>Agile workflow</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">HR Management</div>
              <a href="{p}features.html#attendance"><strong>Attendance</strong><span>Track employee hours</span></a>
              <a href="{p}features.html#leave"><strong>Leave Management</strong><span>Manage time off</span></a>
              <a href="{p}features.html#performance"><strong>Performance</strong><span>Employee reviews</span></a>
            </div>
          </div>
        </li>
        <li>
          <button class="nav-top-link" aria-expanded="false">Solutions
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Business Teams</div>
              <a href="{p}solutions.html#enterprise"><strong>For Enterprises</strong><span>Scale governance and teams</span></a>
              <a href="{p}solutions.html#startups"><strong>For Startups</strong><span>Move fast with one workspace</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Operations</div>
              <a href="{p}solutions.html#hr"><strong>For HR Teams</strong><span>People, attendance and leave</span></a>
              <a href="{p}solutions.html#remote"><strong>For Remote Teams</strong><span>Coordinate distributed work</span></a>
            </div>
          </div>
        </li>
        <li><a href="{p}features.html"{cur('features')}>Features</a></li>
        <li><a href="{p}pricing.html"{cur('pricing')}>Pricing</a></li>
        <li>
          <button class="nav-top-link" aria-expanded="false">Resources
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Learn</div>
              <a href="{p}resources.html#blog"><strong>Blog</strong><span>Product and workflow updates</span></a>
              <a href="{p}resources.html#faq"><strong>FAQ</strong><span>Common questions answered</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Support</div>
              <a href="{p}resources.html#docs"><strong>Documentation</strong><span>Guides and setup help</span></a>
              <a href="{p}resources.html#help"><strong>Help Center</strong><span>Find help for your team</span></a>
              <a href="{p}resources.html#api"><strong>API</strong><span>Connect external systems</span></a>
            </div>
          </div>
        </li>
        <li>
          <button class="nav-top-link" aria-expanded="false">Company
            <svg class="nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="mega">
            <div class="mega-col">
              <div class="mega-col-title">Bitroxia</div>
              <a href="{p}about.html"><strong>About Us</strong><span>Who we are and what we build</span></a>
              <a href="{p}about.html#careers"><strong>Careers</strong><span>Join the team behind PMS</span></a>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Legal</div>
              <a href="{p}contact.html"><strong>Contact</strong><span>Talk to us about your needs</span></a>
              <a href="{p}privacy.html"><strong>Privacy Policy</strong><span>How data is handled</span></a>
              <a href="{p}terms.html"><strong>Terms</strong><span>Usage terms and policies</span></a>
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
      <a href="{p}admin/login.html" class="nav-login">Login</a>
      <a href="{p}contact.html" class="btn btn-primary btn-sm">Request Access</a>
      <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="mobileDrawer">
        <span></span>
      </button>
    </div>
  </div>

  <div class="mobile-drawer" id="mobileDrawer">
    <div class="scrim" data-close-drawer></div>
    <div class="mobile-panel">
      <ul class="mobile-links">
        <li>
          <details>
            <summary>Product <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{p}features.html#tasks" data-close-drawer>Task Management</a>
              <a href="{p}features.html#gantt" data-close-drawer>Gantt Charts</a>
              <a href="{p}features.html#kanban" data-close-drawer>Kanban Boards</a>
              <a href="{p}features.html#attendance" data-close-drawer>Attendance</a>
              <a href="{p}features.html#leave" data-close-drawer>Leave Management</a>
              <a href="{p}features.html#performance" data-close-drawer>Performance</a>
            </div>
          </details>
        </li>
        <li>
          <details>
            <summary>Solutions <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{p}solutions.html#enterprise" data-close-drawer>For Enterprises</a>
              <a href="{p}solutions.html#startups" data-close-drawer>For Startups</a>
              <a href="{p}solutions.html#hr" data-close-drawer>For HR Teams</a>
              <a href="{p}solutions.html#remote" data-close-drawer>For Remote Teams</a>
              <a href="{p}solutions.html#developers" data-close-drawer>For Developers</a>
            </div>
          </details>
        </li>
        <li><a href="{p}features.html" data-close-drawer>Features</a></li>
        <li><a href="{p}pricing.html" data-close-drawer>Pricing</a></li>
        <li>
          <details>
            <summary>Resources <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{p}resources.html#blog" data-close-drawer>Blog</a>
              <a href="{p}resources.html#faq" data-close-drawer>FAQ</a>
              <a href="{p}resources.html#docs" data-close-drawer>Documentation</a>
              <a href="{p}resources.html#help" data-close-drawer>Help Center</a>
              <a href="{p}resources.html#api" data-close-drawer>API</a>
            </div>
          </details>
        </li>
        <li>
          <details>
            <summary>Company <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 9l6 6 6-6"/></svg></summary>
            <div class="mobile-sub">
              <a href="{p}about.html" data-close-drawer>About Us</a>
              <a href="{p}about.html#careers" data-close-drawer>Careers</a>
              <a href="{p}contact.html" data-close-drawer>Contact</a>
              <a href="{p}privacy.html" data-close-drawer>Privacy Policy</a>
              <a href="{p}terms.html" data-close-drawer>Terms</a>
            </div>
          </details>
        </li>
      </ul>
      <div class="mobile-cta">
        <a href="{p}admin/login.html" class="btn btn-secondary btn-block">Login</a>
        <a href="{p}contact.html" class="btn btn-primary btn-block" data-close-drawer>Request Access</a>
      </div>
    </div>
  </div>
</header>"""

def FOOTER(prefix):
    p = prefix
    return f"""<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col footer-brand">
        <a href="{p}index.html" class="brand"><img src="{p}assets/img/logo.png" alt="Bitroxia logo"><span>Bitroxia</span></a>
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
          <li><a href="{p}features.html#tasks">Task Management</a></li>
          <li><a href="{p}features.html#gantt">Gantt Charts</a></li>
          <li><a href="{p}features.html#kanban">Kanban Boards</a></li>
          <li><a href="{p}features.html#attendance">Attendance</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Company</h5>
        <ul>
          <li><a href="{p}about.html">About Us</a></li>
          <li><a href="{p}features.html">Features</a></li>
          <li><a href="{p}pricing.html">Pricing</a></li>
          <li><a href="{p}contact.html">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Resources</h5>
        <ul>
          <li><a href="{p}resources.html#faq">FAQ</a></li>
          <li><a href="{p}resources.html#blog">Blog</a></li>
          <li><a href="{p}resources.html#docs">Documentation</a></li>
          <li><a href="{p}resources.html#help">Help Center</a></li>
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
      <p>© 2026 Bitroxia PMS. All rights reserved.</p>
      <div class="footer-legal">
        <a href="{p}privacy.html">Privacy Policy</a>
        <a href="{p}terms.html">Terms of Service</a>
        <a href="{p}admin/login.html">Staff Login</a>
      </div>
    </div>
  </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
</button>"""

def SCRIPTS(prefix, extra=""):
    p = prefix
    return f"""<script src="{p}assets/js/leads.js"></script>
<script src="{p}assets/js/main.js" defer></script>
{extra}"""

def PAGE(title, desc, prefix, body, extra_css="", extra_js="", active=""):
    return f"""<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
{HEAD(title, desc, prefix, extra_css)}
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>
{NAV(prefix, active)}
<main id="main">
{body}
</main>
{FOOTER(prefix)}
{SCRIPTS(prefix, extra_js)}
</body>
</html>
"""

def write(path, content):
    full = os.path.join(ROOT, path)
    os.makedirs(os.path.dirname(full), exist_ok=True)
    with open(full, "w", encoding="utf-8") as f:
        f.write(content)
    print("wrote", path, len(content), "bytes")


if __name__ == "__main__":
    import content_pages as cp
    import content_admin as ca

    write("features.html", PAGE(
        "Features — Bitroxia PMS",
        "Every module Bitroxia PMS ships: tasks, Gantt, Kanban, attendance, leave, performance, reports, dashboards and analytics.",
        "", cp.features_body(""), active="features"))

    write("solutions.html", PAGE(
        "Solutions — Bitroxia PMS",
        "How Bitroxia PMS fits enterprises, startups, HR teams, remote teams and developers.",
        "", cp.solutions_body("")))

    write("pricing.html", PAGE(
        "Pricing — Bitroxia PMS",
        "Simple, transparent pricing for Bitroxia PMS — every module included at every tier.",
        "", cp.pricing_body(""), active="pricing"))

    write("resources.html", PAGE(
        "Resources — Bitroxia PMS",
        "Blog, FAQ, documentation, help center and API reference for Bitroxia PMS.",
        "", cp.resources_body("")))

    write("about.html", PAGE(
        "About Us — Bitroxia PMS",
        "The story, principles and teams behind Bitroxia PMS.",
        "", cp.about_body("")))

    contact_html, contact_js = cp.contact_body("")
    write("contact.html", PAGE(
        "Contact — Bitroxia PMS",
        "Get in touch about project management, HR, ticketing or reporting needs.",
        "", contact_html, extra_js=contact_js))

    write("privacy.html", PAGE(
        "Privacy Policy — Bitroxia PMS",
        "How Bitroxia PMS collects, uses and protects your data.",
        "", cp.privacy_body("")))

    write("terms.html", PAGE(
        "Terms of Service — Bitroxia PMS",
        "The terms that govern use of Bitroxia PMS.",
        "", cp.terms_body("")))

    write("admin/login.html", ca.admin_login_html())
    write("admin/dashboard.html", ca.admin_dashboard_html())

    print("\nBuild complete.")
