# Mobile layout verification

The shared mobile layer is loaded by the Admin, Super Admin, Developer, public-site, and standalone authentication/subscription layouts. It handles narrow grids and forms, wrapping toolbars, responsive media, horizontal table/tab/board scrolling, viewport-sized dialogs, and mobile sidebar focus. Table action menus use the browser top layer on supported browsers, with a portal fallback.

Business operations, permissions, stored values, and export/print layouts are outside this change. Mobile layout overrides are scoped to screen breakpoints; desktop grid behavior is covered by the component checks.

## Results

- All 356 Blade templates compile with PHP token parsing.
- 244 source page previews: 1,464 cases at 320, 375, and 430 CSS pixels in light and dark themes; no detected viewport overflow failures. Print/PDF views and layout-only partials are excluded as standalone pages.
- Shared interaction checks at 320, 375, 430, 768, 1024, and 1440 pixels pass: sidebar focus and Escape closing, focus restoration, table scrolling, dropdown placement and cleanup, preserved action form values, dialog scrolling, and desktop grid preservation.
- Existing settings theme regression: 1,368 checks at each desktop/phone viewport, no failures.
- Representative local screenshots reviewed; final task-toolbar refinement passed its targeted layout and interaction checks.

## Reproduce

```powershell
D:/xampp/php/php.exe tests/regression/mobile_templates.php
node tests/regression/mobile_workspace_layout.cjs
node tests/regression/settings_theme_contrast.cjs
```

The browser audit requires Microsoft Edge locally. Results are in `mobile-responsive-audit.json` and `mobile-responsive-components.json`. Filtered runs write `mobile-responsive-targeted.json`; use `PMS_MOBILE_FILTER` for a view-path regex, or `PMS_MOBILE_SCREENSHOTS` for an output directory.

These are offline source previews using representative sample text and real view styles, plus isolated shared-component interaction checks. Blade conditional branches are expanded for coverage rather than evaluated against a signed-in account. This is not authenticated end-to-end coverage, proof of every data-dependent layout, or physical-device/Safari testing. Complex charts, calendars, and intentionally scrollable tables are excluded from the general overflow assertion and require signed-in visual review with real records.
