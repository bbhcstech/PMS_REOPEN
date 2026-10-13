# PWA verification

The installed app uses the same Laravel pages, sessions, permissions, and shared mobile layouts as the website. PWA metadata is now included in the remaining application/authentication/subscription layouts. The manifest uses relative URLs, supports landscape, and prefers standalone display.

The worker fetches current shared assets online and falls back to its own cached assets on connection failure. It never caches authenticated HTML, records, uploaded files, or save requests. Failed navigation displays the offline page; reconnecting retries the original URL. Server errors are passed through rather than replaced by the offline screen. Cache cleanup is restricted to this PMS scope and its legacy caches.

Worker updates wait for activation. App mode offers “Reload to update,” with a reminder to save work. Initial worker activation and background updates do not force a reload. Connection notices, safe-area spacing, and install-prompt rejection handling are included. Mutable worker/manifest resources revalidate through Apache headers.

## Results

- 29 actual browser checks passed: worker installation/control, draft preservation, fresh assets, server writes, absence of account data in caches, offline asset fallback/navigation, reconnection, waiting updates, explicit reload, restricted cache cleanup, and manifest/icon dimensions.
- All 244 page previews passed the standalone layout audit: 1,464 cases at 320/375/430 pixels in both themes; shared interaction checks also passed.
- All 356 Blade templates compile. JavaScript syntax and existing tenant cache isolation checks pass.

```powershell
node tests/regression/pwa_browser.cjs
$env:PMS_PWA_MODE='1'
node tests/regression/mobile_workspace_layout.cjs
node tests/regression/tenant_browser_security.cjs
D:/xampp/php/php.exe tests/regression/mobile_templates.php
```

Reports: `pwa-browser-verification.json` and `pwa-responsive-audit.json`.

The browser lifecycle test uses a local fixture server, real service-worker requests/caches, and simulated installed-mode detection. Layout previews use sample content and real view styles. Physical Android/iOS installation, Safari behavior, and authenticated end-to-end workflows have not been tested. Data-changing operations still need an internet connection; no offline writes are queued. Deploy these changes before expecting the production installed app to receive them; an already installed app may offer “Reload to update.”

References: [MDN worker update cache behavior](https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/updateViaCache), [MDN standalone app display](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/How_to/Create_a_standalone_app).
