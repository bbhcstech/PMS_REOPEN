/*
 * Keeps the admin sidebar as the user left it while moving between pages (per browser tab):
 * submenus the user opened stay open, and the sidebar keeps its scroll position instead of
 * jumping back to the top. Menus the server opens for the current page are always kept open.
 */
(function () {
    'use strict';
    var OPEN_KEY = 'pmsSidebarOpenMenus';
    var SCROLL_KEY = 'pmsSidebarScrollTop';
    var menu = document.getElementById('layout-menu');
    if (!menu) return;
    var inner = menu.querySelector('.menu-inner');

    var storage = {
        get: function (key) { try { return window.sessionStorage.getItem(key); } catch (e) { return null; } },
        set: function (key, value) { try { window.sessionStorage.setItem(key, value); } catch (e) { /* storage unavailable */ } }
    };

    function toggleItems() {
        return Array.prototype.filter.call(menu.querySelectorAll('.menu-item'), function (item) {
            return item.querySelector(':scope > .menu-toggle');
        });
    }

    // Stable key: explicit data-sidebar-menu, else the label path (e.g. "Settings > Department").
    function keyFor(item) {
        if (item.dataset.sidebarMenu) return item.dataset.sidebarMenu;
        var parts = [];
        var current = item;
        while (current && current !== menu) {
            if (current.classList && current.classList.contains('menu-item')) {
                var toggle = current.querySelector(':scope > .menu-toggle');
                if (toggle) parts.unshift(toggle.textContent.replace(/\s+/g, ' ').trim());
            }
            current = current.parentElement;
        }
        return parts.join(' > ');
    }

    function saveOpenMenus() {
        var open = toggleItems().filter(function (item) { return item.classList.contains('open'); }).map(keyFor);
        storage.set(OPEN_KEY, JSON.stringify(open));
    }

    function saveScroll() {
        if (inner) storage.set(SCROLL_KEY, String(inner.scrollTop));
    }

    // Restore menus the user had open on the previous page.
    var remembered = [];
    try { remembered = JSON.parse(storage.get(OPEN_KEY) || '[]') || []; } catch (e) { remembered = []; }
    if (remembered.length) {
        toggleItems().forEach(function (item) {
            if (remembered.indexOf(keyFor(item)) !== -1) item.classList.add('open');
        });
    }

    // Record the state after every open/close (after the menu animation has finished).
    menu.addEventListener('click', function (event) {
        if (event.target.closest('.menu-toggle')) setTimeout(saveOpenMenus, 450);
        if (event.target.closest('a.menu-link:not(.menu-toggle)')) { saveOpenMenus(); saveScroll(); }
    });
    if (inner) inner.addEventListener('scroll', function () { saveScroll(); }, { passive: true });
    window.addEventListener('pagehide', function () { saveOpenMenus(); saveScroll(); });

    // Restore the scroll position after main.js has scrolled to the active item.
    function restoreScroll() {
        if (!inner) return;
        var saved = parseInt(storage.get(SCROLL_KEY) || '', 10);
        if (!isNaN(saved)) inner.scrollTop = saved;
        // The current page's link must still be visible.
        var active = menu.querySelector('.menu-item.active:not(.open) > .menu-link');
        if (active) {
            var box = inner.getBoundingClientRect();
            var link = active.getBoundingClientRect();
            if (link.top < box.top || link.bottom > box.bottom) {
                inner.scrollTop += link.top - box.top - (box.height / 2) + (link.height / 2);
            }
        }
        if (window.Helpers && window.Helpers.menuPsScroll) window.Helpers.menuPsScroll.update();
    }
    if (document.readyState === 'complete') setTimeout(restoreScroll, 0);
    else window.addEventListener('load', function () { setTimeout(restoreScroll, 0); });
})();
