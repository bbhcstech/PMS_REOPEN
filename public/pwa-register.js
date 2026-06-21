(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) {
    return;
  }

  var refreshing = false;

  navigator.serviceWorker.addEventListener('controllerchange', function () {
    if (refreshing) {
      return;
    }

    refreshing = true;
    window.location.reload();
  });

  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/sw.js', { scope: '/' })
      .then(function (registration) {
        registration.update();
      })
      .catch(function (error) {
        console.warn('Bitroxia PMS service worker registration failed:', error);
      });
  });
})();
