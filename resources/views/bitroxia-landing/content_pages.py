# -*- coding: utf-8 -*-
from content_icons import ICON_CHECK, ICON_ARROW, ICONS, illus_gantt, illus_kanban, illus_calendar, illus_list, illus_chart

def breadcrumb(trail, prefix):
    parts = []
    for label, href in trail:
        if href:
            parts.append(f'<a href="{prefix}{href}">{label}</a>')
        else:
            parts.append(f'<span>{label}</span>')
    sep = ' <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg> '
    return '<div class="breadcrumb">' + sep.join(parts) + '</div>'

def page_hero(prefix, trail, eyebrow, h1, sub, actions=""):
    return f"""<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"></div>
  <div class="container">
    {breadcrumb(trail, prefix)}
    <div class="page-hero-head" data-reveal>
      <span class="eyebrow">{eyebrow}</span>
      <h1>{h1}</h1>
      <p>{sub}</p>
      {actions}
    </div>
  </div>
</section>"""

def jump_nav(items):
    row = "".join(f'<a href="#{a}">{l}</a>' for l, a in items)
    return f"""<div class="jump-nav"><div class="container"><div class="jump-row">{row}</div></div></div>"""

def module_block(anchor, icon_key, eyebrow, title, desc, bullets, illustration, reverse=False, bg=""):
    rev = " reverse" if reverse else ""
    bullets_html = "".join(f'<li>{ICON_CHECK}<span>{b}</span></li>' for b in bullets)
    return f"""<section class="module-block{rev}" id="{anchor}">
  <div class="container">
    <div class="module-grid">
      <div class="module-copy" data-reveal>
        <div class="module-icon" style="background:{bg}">{ICONS[icon_key]}</div>
        <span class="eyebrow">{eyebrow}</span>
        <h2>{title}</h2>
        <p>{desc}</p>
        <ul class="module-list">{bullets_html}</ul>
      </div>
      <div class="module-media" data-reveal>
        {illustration}
      </div>
    </div>
  </div>
</section>"""

def cta_banner(prefix, headline, sub, primary_label="Request Access", secondary_label="Explore Features", secondary_href="features.html"):
    return f"""<section class="section-tight">
  <div class="container">
    <div class="cta-banner" data-reveal>
      <h2>{headline}</h2>
      <p>{sub}</p>
      <div class="cta-banner-actions">
        <a href="{prefix}contact.html" class="btn btn-primary">{primary_label}</a>
        <a href="{prefix}{secondary_href}" class="btn btn-secondary" style="border-color:rgba(255,255,255,0.25);color:#fff;">{secondary_label}</a>
      </div>
      <p class="fine">Responsive web app · Secure login · Built for daily operations</p>
    </div>
  </div>
</section>"""

GRAD = {
    "blue": "linear-gradient(135deg, var(--brand-blue), #4A7CFF)",
    "purple": "linear-gradient(135deg, var(--brand-purple), #B389FF)",
    "cyan": "linear-gradient(135deg, var(--brand-cyan), #67E8F9)",
    "green": "linear-gradient(135deg, #34D399, #6EE7B7)",
    "amber": "linear-gradient(135deg, #F59E0B, #FBBF24)",
    "rose": "linear-gradient(135deg, #FB7185, #FCA5A5)",
}

# ======================================================================
# FEATURES PAGE
# ======================================================================
def features_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Features", None)],
        "Product",
        "Every module your team needs, in one workspace",
        "Projects, tasks, attendance, leave, tickets, clients and reporting — Bitroxia PMS ships all nine core modules together, so nothing is locked behind a separate add-on.",
        f'<div class="page-hero-actions"><a href="{prefix}contact.html" class="btn btn-primary">Request Access</a><a href="{prefix}index.html#demo" class="btn btn-secondary">Watch Walkthrough</a></div>')

    jump = jump_nav([
        ("Tasks", "tasks"), ("Gantt", "gantt"), ("Kanban", "kanban"), ("Attendance", "attendance"),
        ("Leave", "leave"), ("Performance", "performance"), ("Tickets", "tickets"), ("Reports", "reports"),
        ("Dashboard", "dashboard"), ("Analytics", "analytics"),
    ])

    blocks = []
    blocks.append(module_block("tasks", "tasks", "Project Management", "Task Management",
        "Break work into tasks and subtasks, assign owners, attach files, and keep every comment and status change in one thread instead of scattered across chat apps.",
        ["Custom labels, priorities and due dates", "File attachments and threaded comments", "Subtasks with independent owners", "Status automation on completion"],
        illus_list([
            (GRAD["blue"], "Design onboarding flow", "Due in 2 days", "To Do"),
            (GRAD["purple"], "Client contract review", "Assigned to Priya", "In Progress"),
            (GRAD["cyan"], "QA regression pass", "Blocked · waiting review", "Review"),
        ]), bg=GRAD["blue"]))

    blocks.append(module_block("gantt", "gantt", "Project Management", "Gantt Charts",
        "Plan delivery visually. See how phases overlap, spot dependency conflicts before they cause delays, and drag to reschedule without breaking the rest of the plan.",
        ["Drag-to-reschedule timeline bars", "Dependency lines between tasks", "Milestone markers on the timeline", "Zoom from weeks to full quarters"],
        illus_gantt([
            ("Discovery", 0, 18), ("Design", 12, 22), ("Build — API", 30, 30),
            ("Build — UI", 38, 34), ("QA", 66, 18), ("Launch", 82, 12),
        ]), reverse=True, bg=GRAD["purple"]))

    blocks.append(module_block("kanban", "kanban", "Project Management", "Kanban Boards",
        "Agile teams get a familiar board — drag cards across To Do, In Progress, Review and Done, with WIP limits and swimlanes to keep work visible without a status meeting.",
        ["Unlimited custom columns and swimlanes", "WIP limits with visual warnings", "Card-level labels, checklists and due dates", "One-click board-to-report export"],
        illus_kanban([
            ("To Do", ["Design onboarding flow", "Client contract review"]),
            ("In Progress", ["API integration — v2", "QA regression pass"]),
            ("Done", ["Leave policy update"]),
        ]), bg=GRAD["cyan"]))

    blocks.append(module_block("attendance", "clock", "HR Management", "Attendance &amp; Timelogs",
        "Clock-ins, work hours, task timers and location data come together in one attendance record, so payroll-adjacent reporting doesn't need a separate spreadsheet.",
        ["GPS-aware clock-in for field and remote staff", "Per-task timers roll up to daily totals", "Exportable timesheets by employee or team", "Late/early flags surfaced automatically"],
        illus_calendar((["on","on","on","","off","on","on"] * 3)), reverse=True, bg=GRAD["green"]))

    blocks.append(module_block("leave", "calendar", "HR Management", "Leave &amp; Holidays",
        "Configure leave policies once, then let balances, holiday calendars and approvals run themselves — while capacity planning stays visible to project managers.",
        ["Custom leave types and accrual rules", "Company-wide and location-specific holidays", "Approval routing with manager visibility", "Capacity impact shown before you approve"],
        illus_calendar((["","off","","on","on","","off"] * 3)), bg=GRAD["purple"]))

    blocks.append(module_block("performance", "star", "HR Management", "Performance &amp; Reviews",
        "Run structured review cycles, track goals against outcomes, and give HR a documented record of growth conversations instead of scattered private notes.",
        ["Configurable review cycles and templates", "Goal tracking tied to real project output", "Manager + self-review in one flow", "Award and recognition history per employee"],
        illus_list([
            (GRAD["amber"], "Q3 review — Rohan Das", "Self-review submitted", "Pending"),
            (GRAD["blue"], "Q3 review — Neha Kapoor", "Manager review complete", "Done"),
            (GRAD["green"], "Spot award — delivery", "Homepage redesign team", "Awarded"),
        ]), reverse=True, bg=GRAD["amber"]))

    blocks.append(module_block("tickets", "ticket", "Client Management", "Tickets &amp; Clients",
        "Handle support issues, client records, lead contacts and deals alongside the project that caused them — so context never gets lost between your team and your client.",
        ["Ticket threads linked to the source project", "Client and lead contact records in one place", "Deal and contract stage tracking", "Client-role logins with scoped visibility"],
        illus_list([
            (GRAD["blue"], "Ticket #4821 — Login issue", "Rohan Das · Client Success", "In Progress"),
            (GRAD["purple"], "Vendor contract renewal", "Arjun Mehta · Client", "In Review"),
            (GRAD["green"], "Onboarding call — Acme Co.", "Scheduled Thu 3:00 PM", "Upcoming"),
        ]), reverse=True, bg=GRAD["rose"]))

    blocks.append(module_block("reports", "chart", "Analytics", "Custom Reports",
        "Build the report your team actually checks — filter by project, department or date range, then export or schedule it to land in an inbox automatically.",
        ["Drag-and-drop report builder", "Scheduled exports by email", "Saved filters per department", "CSV, Excel and PDF export formats"],
        illus_chart([40,65,50,80,55,90,70]), bg=GRAD["cyan"]))

    blocks.append(module_block("dashboard", "layers", "Analytics", "Real-time Dashboard",
        "One home screen per role — managers see delivery health, HR sees attendance and leave load, client teams see open tickets — all pulling from the same live data.",
        ["Role-based dashboard layouts", "Live task and ticket counters", "Drill-down from summary to detail", "Configurable widgets per department"],
        illus_list([
            (GRAD["blue"], "124 tasks completed", "This week · +18% vs last", "Live"),
            (GRAD["purple"], "32 of 36 online", "Team availability", "Live"),
            (GRAD["cyan"], "6 tickets open", "Avg response 2.4h", "Live"),
        ]), reverse=True, bg=GRAD["blue"]))

    blocks.append(module_block("analytics", "chart", "Analytics", "Data Analytics",
        "Go beyond a single report — trend delivery velocity, attendance patterns and ticket volume over time to catch problems while they're still small.",
        ["Trend lines across custom date ranges", "Team and department comparisons", "Anomaly flags on sudden spikes", "Full audit log for every record change"],
        illus_chart([55,40,60,35,75,50,85]), bg=GRAD["rose"]))

    cta = cta_banner(prefix, "Ready to see every module together?",
        "Request access and we'll set up a workspace pre-loaded with your team's departments, roles and a sample project.")

    return hero + jump + "".join(blocks) + cta


# ======================================================================
# SOLUTIONS PAGE
# ======================================================================
def solutions_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Solutions", None)],
        "Solutions",
        "Built for how your team is actually structured",
        "Whether you're a five-person startup or a multi-department enterprise, Bitroxia PMS adapts its roles, dashboards and reporting to the way your organization really works.",
        f'<div class="page-hero-actions"><a href="{prefix}contact.html" class="btn btn-primary">Talk to Us</a></div>')

    jump = jump_nav([
        ("Enterprises", "enterprise"), ("Startups", "startups"), ("HR Teams", "hr"),
        ("Remote Teams", "remote"), ("Developers", "developers"),
    ])

    blocks = []
    blocks.append(module_block("enterprise", "shield", "Business Teams", "For Enterprises",
        "Scale governance across departments without losing a single-source-of-truth view. Role-based access, audit logs and contract tracking keep larger orgs accountable.",
        ["Granular roles: admin, employee, client, superadmin", "Full audit log across every module", "Multi-department reporting rollups", "Contract and vendor record tracking"],
        illus_list([
            (GRAD["blue"], "Engineering dept.", "142 members · 18 projects", "Active"),
            (GRAD["purple"], "Client Success dept.", "36 members · 240 tickets", "Active"),
            (GRAD["cyan"], "Finance dept.", "12 members · Payroll sync", "Active"),
        ]), bg=GRAD["blue"]))

    blocks.append(module_block("startups", "rocket", "Business Teams", "For Startups",
        "Move fast with one workspace instead of stitching together five tools. Set up projects, invite your team and start tracking attendance the same afternoon.",
        ["Guided setup in under a day", "Flat pricing with every module included", "No per-feature paywalls between teams", "Scales with you — no re-platforming later"],
        illus_kanban([
            ("To Do", ["MVP scope doc", "Landing page copy"]),
            ("In Progress", ["Auth flow", "Pricing page"]),
            ("Done", ["Logo &amp; brand kit"]),
        ]), reverse=True, bg=GRAD["purple"]))

    blocks.append(module_block("hr", "users", "Operations", "For HR Teams",
        "People, attendance and leave sit next to project data — so capacity planning uses real numbers instead of a manager's best guess.",
        ["Attendance and leave in one policy engine", "Department, designation and holiday setup", "Performance review cycles built in", "Exportable payroll-adjacent reports"],
        illus_calendar((["on","on","","off","on","on",""] * 3)), bg=GRAD["cyan"]))

    blocks.append(module_block("remote", "globe", "Operations", "For Remote Teams",
        "Coordinate distributed work across time zones with location-aware attendance, async-friendly tickets, and dashboards that stay legible on any device.",
        ["Location-based clock-ins for field/remote staff", "Async ticket threads with full context", "Fully responsive app for any screen size", "Availability visible across time zones"],
        illus_list([
            (GRAD["blue"], "Priya Sharma", "Kolkata · Online", "Active"),
            (GRAD["cyan"], "Arjun Mehta", "Bengaluru · Online", "Active"),
            (GRAD["purple"], "Sofia Almeida", "Lisbon · Offline", "Away"),
        ]), reverse=True, bg=GRAD["green"]))

    blocks.append(module_block("developers", "code", "Delivery", "For Developers",
        "Tasks, boards and delivery flow built around how engineering teams actually ship — with an API to connect Bitroxia PMS to the rest of your stack.",
        ["Kanban boards tuned for sprint workflows", "API access for custom integrations", "Webhooks on task and ticket status change", "Exportable activity logs for audits"],
        illus_gantt([
            ("Sprint 12", 0, 24), ("Sprint 13", 22, 24), ("Sprint 14", 44, 24), ("Release", 70, 16),
        ]), bg=GRAD["amber"]))

    cta = cta_banner(prefix, "Not sure which setup fits your team?",
        "Tell us your team size and structure — we'll suggest the right roles, departments and modules to start with.")

    return hero + jump + "".join(blocks) + cta


# ======================================================================
# PRICING PAGE
# ======================================================================
def pricing_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Pricing", None)],
        "Simple Pricing",
        "One plan, every module included",
        "No feature paywalls between departments — projects, HR, tickets and reporting ship together from day one. Figures below are a starting draft; confirm final pricing before publishing.",
        "")

    tiers = f"""<section class="section">
  <div class="container">
    <div class="dept-grid" data-reveal-group>
      <div class="dept-card" data-reveal style="--i:0">
        <span class="tag">Starter</span>
        <h3>&#8377;499<span style="font-size:14px;color:var(--ink-faint);font-weight:500;"> /user/mo</span></h3>
        <p>For small, focused teams running their first projects.</p>
        <ul>
          <li>{ICON_CHECK}Up to 15 team members</li>
          <li>{ICON_CHECK}Tasks, Kanban &amp; attendance</li>
          <li>{ICON_CHECK}Standard reporting</li>
          <li>{ICON_CHECK}Email support</li>
        </ul>
        <div style="margin-top:var(--sp-3)"><a href="{prefix}contact.html" class="btn btn-secondary btn-block">Talk to sales</a></div>
      </div>
      <div class="dept-card" style="border-color:var(--brand-blue)" data-reveal style="--i:1">
        <span class="tag">Growth</span>
        <h3>&#8377;899<span style="font-size:14px;color:var(--ink-faint);font-weight:500;"> /user/mo</span></h3>
        <p>For scaling departments adding HR and client workflows.</p>
        <ul>
          <li>{ICON_CHECK}Up to 100 team members</li>
          <li>{ICON_CHECK}Leave, tickets &amp; clients</li>
          <li>{ICON_CHECK}Custom dashboards</li>
          <li>{ICON_CHECK}Priority support</li>
        </ul>
        <div style="margin-top:var(--sp-3)"><a href="{prefix}contact.html" class="btn btn-primary btn-block">Talk to sales</a></div>
      </div>
      <div class="dept-card" data-reveal style="--i:2">
        <span class="tag">Enterprise</span>
        <h3>Custom</h3>
        <p>For multi-department orgs needing governance at scale.</p>
        <ul>
          <li>{ICON_CHECK}Unlimited team members</li>
          <li>{ICON_CHECK}Contracts &amp; audit logs</li>
          <li>{ICON_CHECK}Dedicated onboarding</li>
          <li>{ICON_CHECK}SLA-backed support</li>
        </ul>
        <div style="margin-top:var(--sp-3)"><a href="{prefix}contact.html" class="btn btn-secondary btn-block">Talk to sales</a></div>
      </div>
    </div>
  </div>
</section>"""

    compare = """<section class="section section-soft">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Compare Plans</span>
      <h2>What's included at each tier</h2>
      <p>Every module ships in every plan — tiers differ mainly by team size, governance depth and support level.</p>
    </div>
    <div class="compare-table-wrap">
      <table class="compare-table">
        <thead><tr><th>Feature</th><th>Starter</th><th>Growth</th><th>Enterprise</th></tr></thead>
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
</section>"""

    faq = """<section class="section" id="faq">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Billing FAQ</span>
      <h2>Common pricing questions</h2>
    </div>
    <div class="faq-list">
      <div class="faq-item is-open">
        <button class="faq-q"><span>Can I switch plans later?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Yes — you can move between Starter, Growth and Enterprise at any time. Your project, task and attendance history carries over automatically.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span>Is there a free trial?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Request access and we'll set up a preview workspace with sample data so your team can evaluate the modules before committing to a plan.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span>Do you offer annual billing?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Yes, annual billing is available on all plans at a discount versus monthly. Ask your sales contact for current annual rates.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span>What happens if I go over my team size limit?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">We'll reach out before any limit is enforced so you can upgrade smoothly — no workspace is ever locked without notice.</div></div>
      </div>
    </div>
  </div>
</section>"""

    cta = cta_banner(prefix, "Get exact pricing for your team", "Share your team size and modules you need — we'll send a tailored quote within one business day.", primary_label="Get a Quote")

    return hero + tiers + compare + faq + cta


# ======================================================================
# RESOURCES PAGE
# ======================================================================
def resources_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Resources", None)],
        "Resources",
        "Guides, updates and answers",
        "Everything you need to get a team running on Bitroxia PMS — product updates, setup guides, support channels and API documentation.",
        "")

    jump = jump_nav([("Blog", "blog"), ("FAQ", "faq"), ("Docs", "docs"), ("Help Center", "help"), ("API", "api")])

    blog = f"""<section class="section" id="blog">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">From the Blog</span>
      <h2>Product and workflow updates</h2>
      <p>Notes on running project, HR and reporting workflows well — from the team building Bitroxia PMS.</p>
    </div>
    <div class="blog-grid" data-reveal-group>
      <article class="blog-card" data-reveal style="--i:0">
        <div class="blog-thumb">{ICONS['layers']}</div>
        <div class="blog-body">
          <div class="blog-meta"><span>Product</span><span>&middot;</span><span>6 min read</span></div>
          <h3>5 Signs Your Team Has Outgrown Spreadsheets</h3>
          <p>How to spot the moment when tracking tasks and attendance in spreadsheets starts costing more time than it saves.</p>
          <a href="#" class="feature-link">Read Article {ICON_ARROW}</a>
        </div>
      </article>
      <article class="blog-card" data-reveal style="--i:1">
        <div class="blog-thumb">{ICONS['kanban']}</div>
        <div class="blog-body">
          <div class="blog-meta"><span>Workflow</span><span>&middot;</span><span>5 min read</span></div>
          <h3>Structuring Approval Workflows Without Slowing Teams Down</h3>
          <p>A practical approach to leave, expense and contract approvals that protects capacity without adding bottlenecks.</p>
          <a href="#" class="feature-link">Read Article {ICON_ARROW}</a>
        </div>
      </article>
      <article class="blog-card" data-reveal style="--i:2">
        <div class="blog-thumb">{ICONS['clock']}</div>
        <div class="blog-body">
          <div class="blog-meta"><span>HR</span><span>&middot;</span><span>7 min read</span></div>
          <h3>An Attendance Checklist for Distributed Teams</h3>
          <p>What to configure before rolling out location-based clock-ins across a remote or hybrid workforce.</p>
          <a href="#" class="feature-link">Read Article {ICON_ARROW}</a>
        </div>
      </article>
    </div>
  </div>
</section>"""

    faq = """<section class="section section-soft" id="faq">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">FAQ</span>
      <h2>Common questions answered</h2>
    </div>
    <div class="faq-list">
      <div class="faq-item is-open">
        <button class="faq-q"><span>How do I invite my team?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">From the admin workspace, add employees by email under HR &gt; Employees. Each invite includes a role (admin, employee, client) that controls what they can see.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span>Can I import existing tasks from another tool?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Yes — CSV import is supported for tasks, employees and client records during onboarding. Our team can help map your existing fields.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q"><span>Is my data backed up?</span><span class="plus"></span></button>
        <div class="faq-a"><div class="faq-a-inner">Production workspaces run on daily automated backups with point-in-time recovery available on Growth and Enterprise plans.</div></div>
      </div>
    </div>
  </div>
</section>"""

    docs = f"""<section class="section" id="docs">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Documentation</span>
      <h2>Guides and setup help</h2>
    </div>
    <div class="util-grid">
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['blue']}">{ICONS['rocket']}</div>
        <h3>Getting Started Guide</h3>
        <p>Set up departments, roles and your first project in under an hour.</p>
        <a href="#" class="feature-link">Read Guide {ICON_ARROW}</a>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['purple']}">{ICONS['layers']}</div>
        <h3>Import &amp; Migration</h3>
        <p>Bring over tasks, employees and client records from your current tools.</p>
        <a href="#" class="feature-link">Read Guide {ICON_ARROW}</a>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['cyan']}">{ICONS['shield']}</div>
        <h3>Roles &amp; Permissions</h3>
        <p>Configure admin, employee and client access scoped to what each role needs.</p>
        <a href="#" class="feature-link">Read Guide {ICON_ARROW}</a>
      </div>
    </div>
  </div>
</section>"""

    help_sec = f"""<section class="section section-soft" id="help">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Help Center</span>
      <h2>Find help for your team</h2>
    </div>
    <div class="util-grid">
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['green']}">{ICONS['life-ring']}</div>
        <h3>Contact Support</h3>
        <p>Reach our team directly for setup help or account issues.</p>
        <a href="{prefix}contact.html" class="feature-link">Contact Us {ICON_ARROW}</a>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['amber']}">{ICONS['chart']}</div>
        <h3>System Status</h3>
        <p>Check current uptime and any ongoing incidents.</p>
        <a href="#" class="feature-link">View Status {ICON_ARROW}</a>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['rose']}">{ICONS['users']}</div>
        <h3>Community</h3>
        <p>Ask questions and share setup tips with other Bitroxia teams.</p>
        <a href="#" class="feature-link">Join Community {ICON_ARROW}</a>
      </div>
    </div>
  </div>
</section>"""

    api = f"""<section class="section" id="api">
  <div class="container">
    <div class="content-block" style="margin-inline:auto;text-align:center;max-width:640px;" data-reveal>
      <span class="eyebrow" style="justify-content:center;">Developers</span>
      <h2>Connect external systems</h2>
      <p>The Bitroxia PMS API lets you read and write tasks, attendance, tickets and client records from your own tools.</p>
    </div>
    <div class="util-grid">
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['blue']}">{ICONS['code']}</div>
        <h3>Authentication</h3>
        <p>Token-based API access scoped per workspace and role.</p>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['purple']}">{ICONS['plug']}</div>
        <h3>Webhooks</h3>
        <p>Subscribe to task, ticket and attendance status changes in real time.</p>
      </div>
      <div class="util-card">
        <div class="feature-icon" style="background:{GRAD['cyan']}">{ICONS['book']}</div>
        <h3>Rate Limits &amp; Docs</h3>
        <p>Full endpoint reference with request and response examples.</p>
      </div>
    </div>
    <div style="text-align:center;margin-top:var(--sp-4);"><a href="{prefix}contact.html" class="btn btn-primary">Request API Access</a></div>
  </div>
</section>"""

    return hero + jump + blog + faq + docs + help_sec + api


# ======================================================================
# ABOUT PAGE
# ======================================================================
def about_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Company", None), ("About Us", None)],
        "Company",
        "Building the operating system for fast-moving teams",
        "Bitroxia PMS started from a simple observation: teams don't fail because they lack tools — they fail because their tools don't talk to each other. We build one connected workspace instead.",
        "")

    story = """<section class="section">
  <div class="container">
    <div class="content-block" data-reveal>
      <span class="eyebrow">Our Story</span>
      <h2>One workspace, built from real operating pain</h2>
      <p>Most growing teams end up running projects in one tool, attendance in another, tickets in a third, and reporting in a spreadsheet that's always a week out of date. Bitroxia PMS was built to close those gaps — connecting delivery, people and client data so managers get one honest picture instead of five partial ones.</p>
      <p>Every module ships together by design. HR data informs project capacity planning. Ticket history stays tied to the project that caused it. Reports pull from the same live records your team already updates every day — nothing is a manual export away from being wrong.</p>
    </div>
  </div>
</section>"""

    pillars = f"""<section class="section section-soft">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">What We Value</span>
      <h2>Principles behind every release</h2>
    </div>
    <div class="pillars-grid" data-reveal-group>
      <div class="pillar-card" data-reveal style="--i:0">
        <div class="pillar-icon">{ICONS['shield']}</div>
        <h3>Reliability First</h3>
        <p>Records stay accurate and available — audit-friendly by default, not as an afterthought.</p>
      </div>
      <div class="pillar-card" data-reveal style="--i:1">
        <div class="pillar-icon">{ICONS['layers']}</div>
        <h3>Clarity Over Clutter</h3>
        <p>Every screen answers one question well instead of trying to show everything at once.</p>
      </div>
      <div class="pillar-card" data-reveal style="--i:2">
        <div class="pillar-icon">{ICONS['users']}</div>
        <h3>Built for Real Teams</h3>
        <p>Features ship because operators asked for them — not because a roadmap needed filling.</p>
      </div>
    </div>
  </div>
</section>"""

    timeline = """<section class="section">
  <div class="container" style="max-width:720px;">
    <div class="section-head">
      <span class="eyebrow">Milestones</span>
      <h2>Where we've been, where we're headed</h2>
      <p>A short version of the roadmap — details subject to change as the product evolves.</p>
    </div>
    <div class="timeline" data-reveal>
      <div class="tl-item"><b>Foundation</b><h4>Core workspace defined</h4><p>Projects, tasks and attendance modules scoped around real day-to-day operating needs.</p></div>
      <div class="tl-item"><b>Expansion</b><h4>HR and client modules added</h4><p>Leave, performance, tickets and client records connected to the same project data.</p></div>
      <div class="tl-item"><b>Today</b><h4>18 core modules, one workspace</h4><p>Dashboards, exports and analytics layered on top for every department.</p></div>
      <div class="tl-item"><b>Next</b><h4>Deeper integrations and API access</h4><p>Connecting Bitroxia PMS to the rest of your stack via webhooks and a public API.</p></div>
    </div>
  </div>
</section>"""

    team = """<section class="section section-soft">
  <div class="container">
    <div class="section-head center">
      <span class="eyebrow">Our Teams</span>
      <h2>The people behind Bitroxia PMS</h2>
      <p>Small, focused teams — each one owning a part of the workspace end to end.</p>
    </div>
    <div class="team-grid" data-reveal-group>
      <div class="team-card" data-reveal style="--i:0"><div class="team-avatar">PT</div><h4>Product Team</h4><span>Roadmap &amp; UX</span></div>
      <div class="team-card" data-reveal style="--i:1"><div class="team-avatar">EN</div><h4>Engineering Team</h4><span>Platform &amp; API</span></div>
      <div class="team-card" data-reveal style="--i:2"><div class="team-avatar">CS</div><h4>Customer Success</h4><span>Onboarding &amp; support</span></div>
      <div class="team-card" data-reveal style="--i:3"><div class="team-avatar">OP</div><h4>Operations Team</h4><span>Reliability &amp; security</span></div>
    </div>
  </div>
</section>"""

    careers = f"""<section class="section" id="careers">
  <div class="container">
    <div class="content-block" style="margin-inline:auto;text-align:center;max-width:600px;" data-reveal>
      <span class="eyebrow" style="justify-content:center;">Careers</span>
      <h2>Join the team behind Bitroxia PMS</h2>
      <p>We're always open to hearing from people who care about building calm, reliable software for operations teams. Reach out with what you'd want to work on.</p>
      <div style="margin-top:var(--sp-3)"><a href="{prefix}contact.html" class="btn btn-primary">Get in Touch</a></div>
    </div>
  </div>
</section>"""

    cta = cta_banner(prefix, "See Bitroxia PMS running", "Request access and explore a workspace pre-loaded with sample projects, tickets and attendance data.")

    return hero + story + pillars + timeline + team + careers + cta


# ======================================================================
# CONTACT PAGE
# ======================================================================
def contact_body(prefix):
    hero = page_hero(prefix,
        [("Home", "index.html"), ("Contact", None)],
        "Contact",
        "Talk to us about your PMS workflow",
        "Need project management, attendance, HR, ticket, client, or reporting customization? Share your requirement and we'll help align modules, roles and reports.",
        "")

    body = f"""<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-info" data-reveal>
        <div class="contact-detail">
          <span class="ic">{ICONS['ticket']}</span>
          <div><b>Email</b><a href="mailto:info@bitroxia.com">info@bitroxia.com</a></div>
        </div>
        <div class="contact-detail">
          <span class="ic">{ICONS['clock']}</span>
          <div><b>Phone</b><a href="tel:+910000000000">+91 00000 00000</a></div>
        </div>
        <div class="contact-detail">
          <span class="ic">{ICONS['globe']}</span>
          <div><b>Delivery</b><span>India and global remote delivery</span></div>
        </div>
        <div class="map-embed">
          <iframe src="https://www.google.com/maps?q=India&output=embed" loading="lazy" title="Business location map" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>

      <form class="contact-form" id="contactFormFull" data-reveal>
        <div class="form-alert" id="contactAlertFull"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span></span></div>
        <div class="form-row">
          <div class="field"><label for="fname2">Full name</label><input id="fname2" data-field="name" type="text" placeholder="Your name" required></div>
          <div class="field"><label for="femail2">Work email</label><input id="femail2" data-field="email" type="email" placeholder="you@company.com" required></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="fcompany2">Company</label><input id="fcompany2" data-field="company" type="text" placeholder="Company name"></div>
          <div class="field"><label for="fsize2">Team size</label>
            <select id="fsize2" data-field="teamSize">
              <option>1&ndash;15</option><option>16&ndash;50</option><option>51&ndash;100</option><option>100+</option>
            </select>
          </div>
        </div>
        <div class="field"><label for="fmsg2">What do you need?</label><textarea id="fmsg2" data-field="message" placeholder="Tell us about your project management, HR, ticketing, or reporting needs"></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
      </form>
    </div>
  </div>
</section>"""

    js = """<script>
document.addEventListener('DOMContentLoaded', function(){
  window.BitroxiaLeads.wireForm(document.getElementById('contactFormFull'), document.getElementById('contactAlertFull'), 'Contact Page');
});
</script>"""

    return hero + body, js


# ======================================================================
# LEGAL PAGES
# ======================================================================
def privacy_body(prefix):
    hero = page_hero(prefix, [("Home","index.html"),("Legal",None),("Privacy Policy",None)],
        "Legal", "Privacy Policy", "How we collect, use and protect data across Bitroxia PMS.", "")
    body = """<section class="section">
  <div class="container">
    <div class="legal-body" data-reveal>
      <p class="legal-updated">Last updated: January 2026 &middot; Draft for legal review before publishing.</p>
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
</section>"""
    return hero + body

def terms_body(prefix):
    hero = page_hero(prefix, [("Home","index.html"),("Legal",None),("Terms",None)],
        "Legal", "Terms of Service", "The terms that govern use of Bitroxia PMS.", "")
    body = """<section class="section">
  <div class="container">
    <div class="legal-body" data-reveal>
      <p class="legal-updated">Last updated: January 2026 &middot; Draft for legal review before publishing.</p>
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
    </div>
  </div>
</section>"""
    return hero + body
