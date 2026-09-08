# Bitroxia PMS — Full Site (Frontend Only + Demo Admin)

A complete, dependency-free static site (HTML/CSS/JS) for Bitroxia PMS: a
10-page marketing site plus a demo Admin dashboard that receives Contact-form
submissions. Colors/type are derived from your uploaded logo; structure
follows the Bitroxia PMS + Tejaswini reference sites you shared.

## Pages
```
index.html         Home
features.html       All 10 product modules (Tasks, Gantt, Kanban, Attendance,
                     Leave, Performance, Tickets & Clients, Reports,
                     Dashboard, Analytics)
solutions.html       Enterprises / Startups / HR Teams / Remote Teams / Developers
pricing.html         Plans, comparison table, billing FAQ
resources.html       Blog, FAQ, Docs, Help Center, API
about.html           Story, values, milestones, teams, careers
contact.html         Full contact page (also mirrored inline on the homepage)
privacy.html         Privacy policy (draft — needs legal review)
terms.html           Terms of service (draft — needs legal review)
admin/login.html     Staff/admin sign-in (demo credentials below)
admin/dashboard.html Admin inbox of Contact-form submissions
```

Every page shares the same nav, mega-menus, footer, theme toggle and mobile
drawer. All in-page anchors (e.g. `features.html#kanban`) and cross-page links
were verified against real element IDs — nothing points to a dead link.

## How the Admin inbox works (demo-grade)
The Contact form on `index.html` and `contact.html` is wired to
`assets/js/leads.js`, which stores each submission as JSON in the browser's
`localStorage` (`bitroxia_requests` key). `admin/dashboard.html` reads that
same key and renders it as a searchable, filterable requests table with a
detail modal, mark-as-read, and delete.

**This is a working prototype, not a production backend.** localStorage is
per-browser and per-origin — it will not sync submissions across different
visitors' devices, and Chrome/Firefox handle `localStorage` differently when
pages are opened directly via `file://`. To test the full flow reliably:

1. Serve the folder over HTTP instead of double-clicking files:
   ```
   cd bitroxia-landing
   python3 -m http.server 8080
   ```
   then open `http://localhost:8080/index.html` in your browser.
2. Submit the Contact form (home page or Contact page).
3. Open `http://localhost:8080/admin/login.html` and sign in.
4. The submission appears in the Requests table.

**Demo admin credentials:**
- Email: `admin@bitroxia.com`
- Password: `bitroxia@2026`

### Before this goes to production
- Replace `assets/js/leads.js`'s localStorage calls with real API calls to
  your Laravel backend (POST the form to an endpoint, store in a `leads`
  table).
- Replace `assets/js/admin.js`'s hardcoded demo credential check with real
  authentication (session/token against your backend) — the current check
  runs entirely in the browser and is not secure.
- Point the map `iframe` and phone/email placeholders to your real details
  (nav, footer, and every contact section currently use placeholders).
- Add `assets/video/bitroxia-demo.mp4` for the homepage video section — it
  currently shows a styled placeholder with working play/pause/mute controls.

## Rebuilding pages after edits
Nine of the eleven pages (`features.html` through `terms.html` +
`admin/*.html`) are generated from `build.py` + `content_pages.py` +
`content_admin.py` + `content_icons.py` so the shared nav/footer/head stay in
sync everywhere. If you edit copy in those `content_*.py` files, regenerate
with:
```
python3 build.py
```
`index.html` itself is hand-authored (not generated) — edit it directly.

## Structure
```
index.html, features.html, solutions.html, pricing.html, resources.html,
about.html, contact.html, privacy.html, terms.html
admin/
  login.html
  dashboard.html
assets/
  css/  base.css, nav-hero.css, sections.css, footer-misc.css,
        pages.css, admin.css, responsive.css
  js/   main.js (nav/theme/carousel/FAQ/video/reveal),
        leads.js (contact-form → localStorage bridge),
        admin.js (admin auth + dashboard rendering)
  img/  logo.png
  video/ (empty — drop your demo mp4 here)
build.py, content_pages.py, content_admin.py, content_icons.py
  (Python generators used to build the 9 templated pages — optional to keep)
```

## Design notes
- Colors: navy base (`#070B1A`) + your logo's blue → violet → cyan gradient,
  used on buttons, icons and stat numbers only — not as a background wash.
- Type: Space Grotesk (headings) + Inter (body).
- All illustrations (Gantt bars, Kanban cards, calendars, activity lists,
  bar charts) are original CSS/SVG mockups in your brand colors — not stock
  photography — since no real product screenshots were provided.
- Pricing figures on `pricing.html` are a placeholder draft — confirm real
  numbers before publishing.
- Light/dark toggle persists via `localStorage` and is available on every
  page (top-right of the nav).
- Mobile menu collapses into a hamburger below 1080px with a full slide-in
  panel and accordion sub-menus; verified down to ~360px.
