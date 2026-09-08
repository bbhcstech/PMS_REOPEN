@extends('frontend.layouts-frontend.app')

@section('title', 'Features — Bitroxia PMS')
@section('meta_description', 'Every module Bitroxia PMS ships: tasks, Gantt, Kanban, attendance, leave, performance, reports, dashboards and analytics.')

@section('content')
<main id="main">
  <section class="page-hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container">
      <div class="breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        <span>Features</span>
      </div>
      <div class="page-hero-head" data-reveal>
        <span class="eyebrow">Product</span>
        <h1>Every module your team needs, in one workspace</h1>
        <p>Projects, tasks, attendance, leave, tickets, clients and reporting — Bitroxia PMS ships all nine core modules together, so nothing is locked behind a separate add-on.</p>
        <div class="page-hero-actions">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Request Access</a>
          <a href="{{ route('home') }}#demo" class="btn btn-secondary">Watch Walkthrough</a>
        </div>
      </div>
    </div>
  </section>

  <div class="jump-nav">
    <div class="container">
      <div class="jump-row">
        <a href="#tasks">Tasks</a>
        <a href="#gantt">Gantt</a>
        <a href="#kanban">Kanban</a>
        <a href="#attendance">Attendance</a>
        <a href="#leave">Leave</a>
        <a href="#performance">Performance</a>
        <a href="#tickets">Tickets</a>
        <a href="#reports">Reports</a>
        <a href="#dashboard">Dashboard</a>
        <a href="#analytics">Analytics</a>
      </div>
    </div>
  </div>

  <!-- 1. Tasks -->
  <section class="module-block" id="tasks">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          </div>
          <span class="eyebrow">Project Management</span>
          <h2>Task Management</h2>
          <p>Break work into tasks and subtasks, assign owners, attach files, and keep every comment and status change in one thread instead of scattered across chat apps.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Custom labels, priorities and due dates</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>File attachments and threaded comments</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Subtasks with independent owners</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Status automation on completion</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>Design onboarding flow</b>Due in 2 days</span><span class="ltag">To Do</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)"></span><span class="ltext"><b>Client contract review</b>Assigned to Priya</span><span class="ltag">In Progress</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)"></span><span class="ltext"><b>QA regression pass</b>Blocked · waiting review</span><span class="ltag">Review</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. Gantt -->
  <section class="module-block reverse" id="gantt">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4v16h18"/><rect x="6" y="14" width="6" height="3" rx="1"/><rect x="10" y="9" width="9" height="3" rx="1"/><rect x="6" y="19" width="12" height="0"/></svg>
          </div>
          <span class="eyebrow">Project Management</span>
          <h2>Gantt Charts</h2>
          <p>Plan delivery visually. See how phases overlap, spot dependency conflicts before they cause delays, and drag to reschedule without breaking the rest of the plan.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Drag-to-reschedule timeline bars</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Dependency lines between tasks</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Milestone markers on the timeline</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Zoom from weeks to full quarters</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-gantt">
              <div class="gantt-row"><span class="label">Discovery</span><div class="track"><i style="left:0%;width:18%"></i></div></div>
              <div class="gantt-row"><span class="label">Design</span><div class="track"><i style="left:12%;width:22%"></i></div></div>
              <div class="gantt-row"><span class="label">Build — API</span><div class="track"><i style="left:30%;width:30%"></i></div></div>
              <div class="gantt-row"><span class="label">Build — UI</span><div class="track"><i style="left:38%;width:34%"></i></div></div>
              <div class="gantt-row"><span class="label">QA</span><div class="track"><i style="left:66%;width:18%"></i></div></div>
              <div class="gantt-row"><span class="label">Launch</span><div class="track"><i style="left:82%;width:12%"></i></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. Kanban -->
  <section class="module-block" id="kanban">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
          </div>
          <span class="eyebrow">Project Management</span>
          <h2>Kanban Boards</h2>
          <p>Agile teams get a familiar board — drag cards across To Do, In Progress, Review and Done, with WIP limits and swimlanes to keep work visible without a status meeting.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Unlimited custom columns and swimlanes</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>WIP limits with visual warnings</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Card-level labels, checklists and due dates</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>One-click board-to-report export</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-kanban">
              <div class="kcol"><span class="klabel">To Do</span><div class="kcard">Design onboarding flow</div><div class="kcard">Client contract review</div></div>
              <div class="kcol"><span class="klabel">In Progress</span><div class="kcard">API integration — v2</div><div class="kcard">QA regression pass</div></div>
              <div class="kcol"><span class="klabel">Done</span><div class="kcard">Leave policy update</div></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. Attendance -->
  <section class="module-block reverse" id="attendance">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #34D399, #6EE7B7)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
          </div>
          <span class="eyebrow">HR Management</span>
          <h2>Attendance &amp; Timelogs</h2>
          <p>Clock-ins, work hours, task timers and location data come together in one attendance record, so payroll-adjacent reporting doesn't need a separate spreadsheet.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>GPS-aware clock-in for field and remote staff</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Per-task timers roll up to daily totals</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Exportable timesheets by employee or team</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Late/early flags surfaced automatically</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-calendar">
              <span class="on"></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
              <span class="on"></span><span class="on"></span><span class="on"></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
              <span class="on"></span><span class="on"></span><span class="on"></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
              <span class="on"></span><span class="on"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. Leave -->
  <section class="module-block" id="leave">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
          </div>
          <span class="eyebrow">HR Management</span>
          <h2>Leave &amp; Holidays</h2>
          <p>Configure leave policies once, then let balances, holiday calendars and approvals run themselves — while capacity planning stays visible to project managers.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Custom leave types and accrual rules</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Company-wide and location-specific holidays</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Approval routing with manager visibility</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Capacity impact shown before you approve</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-calendar">
              <span class=""></span><span class="off"></span><span class=""></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
              <span class=""></span><span class="off"></span><span class=""></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
              <span class=""></span><span class="off"></span><span class=""></span><span class="on"></span><span class="on"></span><span class=""></span><span class="off"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. Performance -->
  <section class="module-block reverse" id="performance">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #F59E0B, #FBBF24)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
          </div>
          <span class="eyebrow">HR Management</span>
          <h2>Performance &amp; Reviews</h2>
          <p>Run structured review cycles, track goals against outcomes, and give HR a documented record of growth conversations instead of scattered private notes.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Configurable review cycles and templates</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Goal tracking tied to real project output</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Manager + self-review in one flow</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Award and recognition history per employee</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, #F59E0B, #FBBF24)"></span><span class="ltext"><b>Q3 review — Rohan Das</b>Self-review submitted</span><span class="ltag">Pending</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>Q3 review — Neha Kapoor</b>Manager review complete</span><span class="ltag">Done</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, #34D399, #6EE7B7)"></span><span class="ltext"><b>Spot award — delivery</b>Homepage redesign team</span><span class="ltag">Awarded</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 7. Tickets -->
  <section class="module-block reverse" id="tickets">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #FB7185, #FCA5A5)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          </div>
          <span class="eyebrow">Client Management</span>
          <h2>Tickets &amp; Clients</h2>
          <p>Handle support issues, client records, lead contacts and deals alongside the project that caused them — so context never gets lost between your team and your client.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Ticket threads linked to the source project</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Client and lead contact records in one place</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Deal and contract stage tracking</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Client-role logins with scoped visibility</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>Ticket #4821 — Login issue</b>Rohan Das · Client Success</span><span class="ltag">In Progress</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)"></span><span class="ltext"><b>Vendor contract renewal</b>Arjun Mehta · Client</span><span class="ltag">In Review</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, #34D399, #6EE7B7)"></span><span class="ltext"><b>Onboarding call — Acme Co.</b>Scheduled Thu 3:00 PM</span><span class="ltag">Upcoming</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 8. Reports -->
  <section class="module-block" id="reports">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l3-4 3 3 5-7"/></svg>
          </div>
          <span class="eyebrow">Analytics</span>
          <h2>Custom Reports</h2>
          <p>Build the report your team actually checks — filter by project, department or date range, then export or schedule it to land in an inbox automatically.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Drag-and-drop report builder</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Scheduled exports by email</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Saved filters per department</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>CSV, Excel and PDF export formats</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-chart">
              <i style="height:40%"></i><i style="height:65%"></i><i style="height:50%"></i>
              <i style="height:80%"></i><i style="height:55%"></i><i style="height:90%"></i><i style="height:70%"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 9. Dashboard -->
  <section class="module-block reverse" id="dashboard">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l9 5-9 5-9-5 9-5z"/><path d="M3 12l9 5 9-5M3 16.5l9 5 9-5"/></svg>
          </div>
          <span class="eyebrow">Analytics</span>
          <h2>Real-time Dashboard</h2>
          <p>One home screen per role — managers see delivery health, HR sees attendance and leave load, client teams see open tickets — all pulling from the same live data.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Role-based dashboard layouts</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Live task and ticket counters</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Drill-down from summary to detail</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Configurable widgets per department</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-list">
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-blue), #4A7CFF)"></span><span class="ltext"><b>124 tasks completed</b>This week · +18% vs last</span><span class="ltag">Live</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-purple), #B389FF)"></span><span class="ltext"><b>32 of 36 online</b>Team availability</span><span class="ltag">Live</span></div>
              <div class="lrow"><span class="ldot" style="background:linear-gradient(135deg, var(--brand-cyan), #67E8F9)"></span><span class="ltext"><b>6 tickets open</b>Avg response 2.4h</span><span class="ltag">Live</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 10. Analytics -->
  <section class="module-block" id="analytics">
    <div class="container">
      <div class="module-grid">
        <div class="module-copy" data-reveal>
          <div class="module-icon" style="background:linear-gradient(135deg, #FB7185, #FCA5A5)">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l3-4 3 3 5-7"/></svg>
          </div>
          <span class="eyebrow">Analytics</span>
          <h2>Data Analytics</h2>
          <p>Go beyond a single report — trend delivery velocity, attendance patterns and ticket volume over time to catch problems while they're still small.</p>
          <ul class="module-list">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Trend lines across custom date ranges</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Team and department comparisons</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Anomaly flags on sudden spikes</span></li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg><span>Full audit log for every record change</span></li>
          </ul>
        </div>
        <div class="module-media" data-reveal>
          <div class="illus-frame">
            <div class="illus-dots"><span></span><span></span><span></span></div>
            <div class="illus-chart">
              <i style="height:55%"></i><i style="height:40%"></i><i style="height:60%"></i>
              <i style="height:35%"></i><i style="height:75%"></i><i style="height:50%"></i><i style="height:85%"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="section-tight">
    <div class="container">
      <div class="cta-banner" data-reveal>
        <h2>Ready to see every module together?</h2>
        <p>Request access and we'll set up a workspace pre-loaded with your team's departments, roles and a sample project.</p>
        <div class="cta-banner-actions">
          <a href="{{ route('company.contact') }}" class="btn btn-primary">Request Access</a>
          <a href="{{ route('features') }}" class="btn btn-secondary" style="border-color:rgba(255,255,255,0.25);color:#fff;">Explore Features</a>
        </div>
        <p class="fine">Responsive web app · Secure login · Built for daily operations</p>
      </div>
    </div>
  </section>
</main>
@endsection
