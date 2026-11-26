(function () {
    'use strict';
    var opened = null;

    function position(state) {
        var menu = state.menu;
        var rect = state.toggle.getBoundingClientRect();
        var gap = 6, edge = 12;
        menu.style.setProperty('position', 'fixed', 'important');
        menu.style.setProperty('inset', 'auto', 'important');
        menu.style.setProperty('margin', '0', 'important');
        menu.style.setProperty('transform', 'none', 'important');
        menu.style.setProperty('z-index', '1080', 'important');
        menu.style.setProperty('max-width', 'calc(100vw - 24px)', 'important');
        menu.style.setProperty('max-height', 'calc(100vh - 24px)', 'important');
        menu.style.setProperty('overflow-y', 'auto', 'important');
        var width = menu.offsetWidth;
        var height = menu.offsetHeight;
        var top = rect.bottom + gap;
        if (top + height > window.innerHeight - edge) top = rect.top - height - gap;
        top = Math.max(edge, Math.min(top, window.innerHeight - height - edge));
        var left = Math.max(edge, Math.min(rect.right - width, window.innerWidth - width - edge));
        menu.style.setProperty('left', left + 'px', 'important');
        menu.style.setProperty('top', top + 'px', 'important');
    }

    document.addEventListener('shown.bs.dropdown', function (event) {
        var toggle = event.target;
        var wrapper = toggle.closest('.recruitment-actions-dropdown');
        if (!wrapper) return;
        var menu = wrapper.querySelector('.recruitment-actions-menu');
        if (!menu) return;
        var state = { toggle: toggle, menu: menu, originalStyle: menu.getAttribute('style') };
        opened = state;
        if (typeof menu.showPopover === 'function') {
            // Top-layer rendering escapes table overflow without moving its forms.
            menu.setAttribute('popover', 'manual');
            menu.showPopover();
        } else {
            // Older browsers use a body portal; Bootstrap retains its menu reference.
            state.anchor = document.createComment('recruitment actions');
            menu.before(state.anchor);
            document.body.appendChild(menu);
        }
        position(state);
    });

    document.addEventListener('hidden.bs.dropdown', function (event) {
        if (!opened || opened.toggle !== event.target) return;
        var state = opened;
        opened = null;
        if (typeof state.menu.hidePopover === 'function' && state.menu.matches(':popover-open')) state.menu.hidePopover();
        state.menu.removeAttribute('popover');
        if (state.anchor) state.anchor.replaceWith(state.menu);
        if (state.originalStyle === null) state.menu.removeAttribute('style');
        else state.menu.setAttribute('style', state.originalStyle);
    });

    function close(event) {
        if (!opened || (event && opened.menu.contains(event.target))) return;
        window.bootstrap.Dropdown.getInstance(opened.toggle)?.hide();
    }
    // Bootstrap's keyboard lookup relies on sibling markup in older browsers.
    document.addEventListener('keydown', function (event) {
        if (!opened || !opened.anchor || !opened.menu.contains(event.target)) return;
        if (!['ArrowUp', 'ArrowDown', 'Escape'].includes(event.key)) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (event.key === 'Escape') {
            var toggle = opened.toggle;
            close();
            toggle.focus();
            return;
        }
        var items = Array.from(opened.menu.querySelectorAll('.dropdown-item:not(.disabled):not(:disabled)'))
            .filter(function (item) { return item.getClientRects().length; });
        if (!items.length) return;
        var index = items.indexOf(event.target);
        index = index < 0 ? (event.key === 'ArrowUp' ? items.length - 1 : 0)
            : Math.max(0, Math.min(items.length - 1, index + (event.key === 'ArrowUp' ? -1 : 1)));
        items[index].focus();
    }, true);
    window.addEventListener('resize', close);
    document.addEventListener('scroll', close, true);
})();
