# Shared operation loading

The application previously left loading feedback to each page. Buttons using fetch, jQuery/XHR, native form submissions, and navigation consequently behaved differently. The shared loader is included once from `partials/pwa.blade.php`, used by the admin/HR/employee, Super Admin, developer, authentication, frontend, and Gantt layouts. It has no viewport breakpoint or external dependency.

`pms-operation-loading.js` associates click/change/submit interactions with requests. It restores button state on response-body completion, HTTP/network failures, aborts, and XHR completion. Reference counts keep parallel requests busy; ordinary fetch promise/await chains retain the triggering control. Background polling has no triggering interaction and remains quiet. Existing module spinners are retained. Fetch responses, request payloads, credentials, errors, CSRF, routes, and authorization are not changed.

The inline spinner occupies the control's corner without replacing its content or changing its dimensions. A nonblocking page progress bar appears after 120ms, with a polite screen-reader announcement. Reduced-motion preferences are supported. Native form submissions use a logical lock instead of disabling the submitter, preserving its name/value in the submitted payload. Async requests disable their trigger and block repeat form submissions. Navigation resets on page restoration and the browser Navigation API's completion/error events; Escape clears canceled page navigation feedback.

For asynchronous work that does not use fetch/XHR (for example a deliberate timer, camera workflow, or third-party callback), keep its existing feedback or use the shared API around the complete operation:

```js
await PMSLoading.run(button, async () => {
    await operation();
});
```

`PMSLoading.begin(button)` returns an idempotent completion callback for callback-based work; always call it in success and error paths. `data-pms-loading="off"` opts a control/form out. `PMSLoading.reset()` clears state for custom navigation lifecycles. No network timeout is imposed by this layer; request cancellation/timeouts remain owned by the existing operation.

Run `node tests/regression/operation_loading_browser.cjs` for isolated Edge/Chromium browser checks at 390px, 820px, and 1440px, including touch input, response downloads, parallel/chained calls, errors/aborts, duplicate actions, AJAX/native/programmatic forms, submitter values, navigation, validation cancellation, instant actions, and restored pages. The fixture never connects to application databases. No environment variables or database migrations are required.
