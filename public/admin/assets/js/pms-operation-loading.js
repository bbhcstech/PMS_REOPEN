(function () {
    'use strict';
    if (window.PMSLoading) return;
    var states = new Map(), forms = new Map(), requestForms = new Map(), context = null, contextTimer;
    var navigation = null, progress, announcement, progressTimer;
    var selector = 'button,input[type="submit"],input[type="button"],input[type="image"],a[href],[role="button"],select';
    function ignored(element) { return element && element.closest('[data-pms-loading="off"]'); }
    function updateProgress() {
        clearTimeout(progressTimer);
        var active = states.size || navigation;
        if (!active) {
            if (progress) progress.hidden = true;
            if (announcement) announcement.textContent = '';
            return;
        }
        // Fast operations finish without flashing a page-level indicator.
        progressTimer = setTimeout(function () {
            if (!document.body || (!states.size && !navigation)) return;
            if (!progress) {
                progress = document.createElement('div'); progress.id = 'pms-operation-progress';
                progress.setAttribute('aria-hidden', 'true'); document.body.appendChild(progress);
                announcement = document.createElement('div'); announcement.className = 'pms-operation-announcement';
                announcement.setAttribute('role', 'status'); announcement.setAttribute('aria-live', 'polite');
                document.body.appendChild(announcement);
            }
            progress.hidden = false; announcement.textContent = 'Loading. Please wait.';
        }, 120);
    }
    function begin(element, disable) {
        if (!element || ignored(element)) return function () {};
        var state = states.get(element);
        if (!state) {
            state = { count: 0, busy: element.getAttribute('aria-busy'), disabled: element.disabled,
                ownsDisabled: false, inline: false };
            states.set(element, state);
            element.setAttribute('aria-busy', 'true'); element.setAttribute('data-pms-operation-busy', '');
            // Existing module spinners retain their own labels and presentation.
            if (!element.matches('input,select,textarea') && !element.querySelector('.spinner-border,.spinner-grow,[data-loading-spinner],.fa-spinner,.bx-loader')) {
                element.classList.add('pms-operation-inline'); state.inline = true;
            }
        }
        if (disable && 'disabled' in element && !element.disabled) {
            element.disabled = true; state.ownsDisabled = true;
        }
        var form = disable && (element.form || element.closest('form'));
        if (form) requestForms.set(form, (requestForms.get(form) || 0) + 1);
        state.count++; updateProgress();
        var done = false;
        return function () {
            if (done) return; done = true;
            if (states.get(element) !== state) return;
            if (form) {
                var remaining = (requestForms.get(form) || 1) - 1;
                if (remaining) requestForms.set(form, remaining); else requestForms.delete(form);
            }
            if (--state.count > 0) return;
            if (state.ownsDisabled) element.disabled = state.disabled;
            if (state.busy === null) element.removeAttribute('aria-busy'); else element.setAttribute('aria-busy', state.busy);
            element.removeAttribute('data-pms-operation-busy');
            if (state.inline) element.classList.remove('pms-operation-inline');
            states.delete(element); updateProgress();
        };
    }
    function remember(element) {
        context = ignored(element) ? null : element;
        clearTimeout(contextTimer);
        contextTimer = setTimeout(function () { context = null; }, 0);
    }
    function navigate(element, form) {
        if (navigation) {
            if (form) { navigation.form = form; forms.set(form, true); }
            return;
        }
        // Native submitter values must remain successful form controls: use a logical lock,
        // rather than disabling the submitter before the browser serializes its name/value.
        navigation = { finish: begin(element, false), form: form };
        if (form) forms.set(form, true);
        updateProgress();
    }
    function resetNavigation() {
        if (navigation) { navigation.finish(); if (navigation.form) forms.delete(navigation.form); }
        navigation = null; updateProgress();
    }
    function reset() {
        resetNavigation();
        states.forEach(function (state, element) {
            state.count = 1;
            if (state.ownsDisabled) element.disabled = state.disabled;
            if (state.busy === null) element.removeAttribute('aria-busy'); else element.setAttribute('aria-busy', state.busy);
            element.removeAttribute('data-pms-operation-busy');
            if (state.inline) element.classList.remove('pms-operation-inline');
        });
        states.clear(); forms.clear(); requestForms.clear(); updateProgress();
    }
    document.addEventListener('click', function (event) {
        var element = event.target.closest && event.target.closest(selector);
        if (!element || ignored(element)) return;
        if (states.has(element) || (element.form && (forms.has(element.form) || requestForms.has(element.form)))) {
            event.preventDefault(); event.stopImmediatePropagation(); return;
        }
        remember(element);
        var link = element.closest('a[href]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
            || link.getAttribute('href').trim().startsWith('#')
            || link.hasAttribute('download') || (link.target && link.target !== '_self')
            || link.matches('[data-bs-toggle],[data-toggle]')) return;
        var url;
        try { url = new URL(link.href, location.href); } catch (_) { return; }
        if (!/^https?:$/.test(url.protocol) || (url.pathname === location.pathname && url.search === location.search && url.hash)) return;
        queueMicrotask(function () { if (!event.defaultPrevented) navigate(link); });
    }, true);
    document.addEventListener('change', function (event) {
        var element = event.target.closest && event.target.closest('select,input');
        if (element) remember(element);
    }, true);
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (ignored(form)) return;
        if (forms.has(form) || requestForms.has(form)) { event.preventDefault(); event.stopImmediatePropagation(); return; }
        var button = event.submitter || form.querySelector('button[type="submit"],button:not([type]),input[type="submit"]');
        remember(button);
        // Set before handlers run so immediate repeated submissions cannot race.
        forms.set(form, event);
        queueMicrotask(function () {
            if (event.defaultPrevented) { if (!navigation || navigation.form !== form) forms.delete(form); return; }
            var target = button && button.getAttribute('formtarget') || form.target;
            var method = button && button.getAttribute('formmethod') || form.method;
            if ((target && target !== '_self') || method === 'dialog') { forms.delete(form); return; }
            navigate(button, form);
        });
    }, true);
    // Several modules intentionally use form.submit() after camera/confirmation/validation.
    var nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (ignored(this)) return nativeSubmit.call(this);
        if (forms.get(this) === true || requestForms.has(this)) return;
        if (!this.target || this.target === '_self') navigate(context || this.querySelector('[type="submit"],button:not([type])'), this);
        try { return nativeSubmit.call(this); } catch (error) { resetNavigation(); throw error; }
    };
    // Fetch and XHR are shared by native JS, jQuery and Axios. Background polling
    // has no interaction context and deliberately does not flash a loading state.
    if (window.fetch) {
        var nativeFetch = window.fetch;
        window.fetch = function () {
            var source = context, finish = begin(source, true), args = arguments;
            var result;
            try { result = nativeFetch.apply(this, args); } catch (error) { finish(); throw error; }
            return result.then(function (response) {
                if (source) remember(source);
                var consuming = false;
                ['json', 'text', 'blob', 'arrayBuffer', 'formData', 'bytes'].forEach(function (method) {
                    if (typeof response[method] !== 'function') return;
                    var original = response[method];
                    response[method] = function () {
                        consuming = true;
                        try { return original.apply(this, arguments).finally(function () { if (source) remember(source); finish(); }); }
                        catch (error) { finish(); throw error; }
                    };
                });
                setTimeout(function () { if (!consuming) finish(); }, 0);
                return response;
            }, function (error) { if (source) remember(source); finish(); throw error; });
        };
    }
    var nativeSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function () {
        var source = context, finish = begin(source, true);
        this.addEventListener('loadend', function () { if (source) remember(source); finish(); }, { once: true });
        try { return nativeSend.apply(this, arguments); } catch (error) { finish(); throw error; }
    };
    // Navigation API reports canceled/failed same-window navigation (including
    // downloads); pageshow also restores controls when returning from bfcache.
    if (window.navigation) {
        window.navigation.addEventListener('navigate', function (event) {
            if (event.downloadRequest != null) { resetNavigation(); return; }
            if (event.hashChange) return;
            var element = context || event.sourceElement;
            queueMicrotask(function () { if (!event.defaultPrevented) navigate(element); });
        });
        window.navigation.addEventListener('navigateerror', resetNavigation);
        window.navigation.addEventListener('navigatesuccess', resetNavigation);
    }
    window.addEventListener('pageshow', reset);
    window.addEventListener('pagehide', reset);
    window.addEventListener('keydown', function (event) { if (event.key === 'Escape') resetNavigation(); });
    window.PMSLoading = {
        begin: function (element) { return begin(element, true); },
        run: function (element, operation) {
            if (states.has(element)) return Promise.resolve();
            var finish = begin(element, true);
            try { return Promise.resolve(operation()).finally(finish); }
            catch (error) { finish(); return Promise.reject(error); }
        },
        reset: reset
    };
})();
