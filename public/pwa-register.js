(function () {
  'use strict';
  if (window.PMSPWA) return;
  var script = document.currentScript;
  var base = new URL('./', script ? script.src : document.baseURI);
  var modes = window.matchMedia('(display-mode: standalone), (display-mode: fullscreen)');
  var registration, updateRequested = false, reloading = false, notice;
  function standalone() { return modes.matches || navigator.standalone === true; }
  function render() {
    document.documentElement.toggleAttribute('data-pms-standalone', standalone());
    if (!document.body) return;
    if (!notice) {
      notice = document.createElement('div');notice.className = 'pms-pwa-notice';notice.hidden = true;
      notice.setAttribute('role', 'status');notice.setAttribute('aria-live', 'polite');
      var text = document.createElement('span');text.className = 'pms-pwa-notice-text';notice.appendChild(text);
      var button = document.createElement('button');button.type = 'button';button.textContent = 'Reload to update';
      button.addEventListener('click', function () {
        if (!registration?.waiting || updateRequested) return;
        updateRequested = true;button.disabled = true;
        registration.waiting.postMessage({type:'SKIP_WAITING'});
      });notice.appendChild(button);document.body.appendChild(notice);
    }
    var offline = navigator.onLine === false, waiting = Boolean(registration?.waiting);
    notice.hidden = !standalone() || (!offline && !waiting);
    notice.querySelector('span').textContent = offline ? 'Connection unavailable. Reconnect to load pages and save changes.' : 'An app update is ready. Save your work before reloading.';
    notice.querySelector('button').hidden = offline || !waiting;
    document.documentElement.style.setProperty('--pms-pwa-notice-space',notice.hidden ? '0px' : (notice.offsetHeight + 24) + 'px');
  }
  window.PMSPWA = {isStandalone:standalone};
  if (modes.addEventListener) modes.addEventListener('change',render);
  window.addEventListener('online',function () {render();checkUpdate();});
  window.addEventListener('offline',render);
  document.addEventListener('visibilitychange',function () {if (!document.hidden) {render();checkUpdate();}});
  function checkUpdate() {
    if (registration && navigator.onLine !== false) registration.update().catch(function () {});
  }
  if ('serviceWorker' in navigator && window.isSecureContext) {
    navigator.serviceWorker.addEventListener('controllerchange',function () {
      // First install and background updates must not discard forms or active timers.
      if (!updateRequested || reloading) return;
      reloading = true;window.location.reload();
    });
  }
  function init() {
    render();
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
    navigator.serviceWorker.register(new URL('sw.js',base).href,{scope:base.href,updateViaCache:'none'})
      .then(function (value) {
        registration = value;render();checkUpdate();
        registration.addEventListener('updatefound',function () {
          var worker = registration.installing;
          if (worker) worker.addEventListener('statechange',render);
        });
      }).catch(function (error) {console.warn('PMS service worker registration failed:',error);});
  }
  if (document.readyState === 'complete') init();
  else window.addEventListener('load',init,{once:true});
})();
