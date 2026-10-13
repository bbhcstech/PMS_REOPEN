(function () {
    'use strict';
    if (window.PMSMobileLayout) return;
    var phone = window.matchMedia('(max-width: 767.98px)');
    var scheduled = false;
    var openMenu = null;
    var activeDrawer = null;
    var previousFocus = null;
    var skipGrid = 'table,.fc,.gantt,.gantt-container,.gantt-matrix-wrapper,.calendar-grid,.calendar-table,.ac-calendar,.heatmap,.apexcharts-canvas,[data-pms-mobile-grid="preserve"]';

    function scan() {
        scheduled = false;
        if (!phone.matches) return;
        var header = document.querySelector('#layout-navbar,.top-header,.dev-topbar');
        if (header) document.documentElement.style.setProperty('--pms-mobile-header-bottom', Math.ceil(header.getBoundingClientRect().bottom) + 'px');
        document.querySelectorAll('div,section,form,main').forEach(function (grid) {
            if (grid.closest(skipGrid) || grid.closest('#layout-menu,.sidebar,.dev-sidebar')) return;
            if (grid.hasAttribute('data-pms-mobile-grid')) return;
            var style = getComputedStyle(grid);
            if (style.display !== 'grid' || !grid.getClientRects().length) return;
            var columns = style.gridTemplateColumns.split(' ').filter(Boolean);
            if (columns.length < 2) return;
            var widths = columns.map(parseFloat);
            var hasFields = Boolean(grid.querySelector('input:not([type="hidden"]),select,textarea'));
            var hasCards = Boolean(grid.querySelector('.card,.stat-card,.metric-card,.dev-card,.analytics-card,.chart-card-wrapper,.kpi-card,h2,h3,h4,h5,h6'));
            var tracksWidth = widths.reduce(function (total, width) { return total + width; }, 0) + (parseFloat(style.columnGap) || 0) * (columns.length - 1);
            if ((hasFields || hasCards) && (Math.min.apply(null, widths) < 180 || tracksWidth > grid.clientWidth + 2)) {
                grid.setAttribute('data-pms-mobile-grid', 'stack');
            }
        });
        document.querySelectorAll('div,section,form').forEach(function (element) {
            if (element.closest(skipGrid + ',#layout-navbar,.top-header,.dev-topbar,#layout-menu,.sidebar,.dev-sidebar,.kanban-board,.kanban-container,.kanban-wrapper,.board-columns')) return;
            if (!element.getClientRects().length) return;
            var style = getComputedStyle(element);
            if (style.position === 'fixed' || style.position === 'absolute') return;
            if (style.display === 'flex' && style.flexDirection === 'row' && style.flexWrap === 'nowrap'
                && element.scrollWidth > element.clientWidth + 2 && !element.matches('.input-group,.input-group-custom,.phone-combo-group,.stat-icon,[class*="avatar"],[class*="logo"]')) {
                element.setAttribute('data-pms-mobile-flex', 'wrap');
            }
            var parent = element.parentElement;
            if (parent && !element.matches('[class*="avatar"],[class*="logo"],.stat-icon,.header-icon,.header-icon-badge')
                && element.getBoundingClientRect().width > parent.clientWidth + 2) {
                element.setAttribute('data-pms-mobile-fluid', '');
            }
        });
        document.querySelectorAll('.kanban-board,.kanban-container,.kanban-wrapper,.kanban-row,.board-columns,.board-container').forEach(function (host) {
            host.setAttribute('data-pms-mobile-scroll','board');
        });
        document.querySelectorAll('.nav-tabs-container,.doc-nav-pills,.section-tabs,.tabs-bar,.tab-nav-container').forEach(function (host) {
            host.setAttribute('data-pms-mobile-scroll','tabs');
        });
        document.querySelectorAll('table').forEach(function (table) {
            if (table.closest('.fc,.gantt,.calendar-table,.daterangepicker')) return;
            var host = table.closest('.table-responsive,.table-wrapper,.pms-table-scroll-host,.matrix-scroll,.card-table-wrap') || table.parentElement;
            if (host && !host.matches('body,main,.content-wrapper,.main,.content,.dev-body-content,td,th')) {
                host.setAttribute('data-pms-mobile-scroll', 'table');
            }
        });
    }

    function schedule() {
        if (scheduled) return;
        scheduled = true;
        requestAnimationFrame(scan);
    }

    function drawerState() {
        var drawer = null;
        if (window.innerWidth < 1200 && document.documentElement.classList.contains('layout-menu-expanded')) drawer = document.getElementById('layout-menu');
        if (window.innerWidth <= 992) drawer = drawer || document.querySelector('#sidebar.open,#devSidebar.open');
        if (drawer === activeDrawer) return;
        var old = activeDrawer;
        activeDrawer = drawer;
        if (drawer) {
            previousFocus = document.activeElement;
            document.body.setAttribute('data-pms-drawer-open', '');
            var first = drawer.querySelector('button,a[href],input,[tabindex="0"]');
            if (first) first.focus({preventScroll: true});
        } else {
            document.body.removeAttribute('data-pms-drawer-open');
            if (old && previousFocus && previousFocus.isConnected) previousFocus.focus({preventScroll: true});
        }
        document.querySelectorAll('#hamburgerBtn,#mobMenuTrigger,.toggle-sidebar-btn,#layout-navbar .layout-menu-toggle a')
            .forEach(function (button) { button.setAttribute('aria-expanded', drawer ? 'true' : 'false'); });
    }

    function closeDrawer() {
        document.documentElement.classList.remove('layout-menu-expanded');
        document.body.classList.remove('layout-menu-expanded', 'dev-sidebar-open');
        document.querySelectorAll('#sidebar.open,#devSidebar.open').forEach(function (drawer) { drawer.classList.remove('open'); });
        document.querySelectorAll('#sidebarOverlay.open,#devSidebarOverlay.show').forEach(function (overlay) { overlay.classList.remove('open','show'); });
        drawerState();
    }

    function closeMenu() {
        if (openMenu && window.bootstrap) window.bootstrap.Dropdown.getInstance(openMenu.toggle)?.hide();
    }

    document.addEventListener('shown.bs.dropdown', function (event) {
        if (!phone.matches) return;
        var toggle = event.target;
        var wrapper = toggle.closest('.dropdown,.btn-group');
        if (!wrapper || wrapper.closest('.recruitment-actions-dropdown') || !wrapper.closest('table,[data-pms-mobile-scroll]')) return;
        var menu = wrapper.querySelector('.dropdown-menu');
        if (!menu) return;
        var state = {toggle:toggle, menu:menu, style:menu.getAttribute('style')};
        openMenu = state;
        if (typeof menu.showPopover === 'function') {
            menu.setAttribute('popover','manual');
            menu.showPopover();
        } else {
            state.anchor = document.createComment('mobile table actions');
            menu.before(state.anchor);
            document.body.appendChild(menu);
        }
        menu.style.setProperty('position','fixed','important');
        menu.style.setProperty('inset','auto','important');
        menu.style.setProperty('transform','none','important');
        menu.style.setProperty('margin','0','important');
        menu.style.setProperty('max-width','calc(100vw - 24px)','important');
        menu.style.setProperty('max-height','calc(100dvh - 24px)','important');
        menu.style.setProperty('overflow-y','auto','important');
        menu.style.setProperty('z-index','1080','important');
        var rect = toggle.getBoundingClientRect();
        var top = rect.bottom + 6;
        if (top + menu.offsetHeight > window.innerHeight - 12) top = rect.top - menu.offsetHeight - 6;
        menu.style.setProperty('top',Math.max(12,Math.min(top,window.innerHeight-menu.offsetHeight-12))+'px','important');
        menu.style.setProperty('left',Math.max(12,Math.min(rect.right-menu.offsetWidth,window.innerWidth-menu.offsetWidth-12))+'px','important');
    });

    document.addEventListener('hidden.bs.dropdown', function (event) {
        if (!openMenu || openMenu.toggle !== event.target) return;
        var state = openMenu;
        openMenu = null;
        if (typeof state.menu.hidePopover === 'function' && state.menu.matches(':popover-open')) state.menu.hidePopover();
        state.menu.removeAttribute('popover');
        if (state.anchor) state.anchor.replaceWith(state.menu);
        if (state.style === null) state.menu.removeAttribute('style');
        else state.menu.setAttribute('style',state.style);
    });

    document.addEventListener('keydown', function (event) {
        if (openMenu && openMenu.anchor && openMenu.menu.contains(event.target) && ['ArrowUp','ArrowDown','Escape'].includes(event.key)) {
            event.preventDefault();event.stopImmediatePropagation();
            if (event.key === 'Escape') {var toggle=openMenu.toggle;closeMenu();toggle.focus();return;}
            var options=Array.from(openMenu.menu.querySelectorAll('.dropdown-item:not(:disabled):not(.disabled)')).filter(function (option) {return option.getClientRects().length;});
            var index=options.indexOf(event.target);
            var next=index<0?(event.key==='ArrowUp'?options.length-1:0):Math.max(0,Math.min(options.length-1,index+(event.key==='ArrowUp'?-1:1)));
            options[next]?.focus();return;
        }
        if (!activeDrawer) return;
        if (event.key === 'Escape') {event.preventDefault();closeDrawer();return;}
        if (event.key !== 'Tab') return;
        var items=Array.from(activeDrawer.querySelectorAll('a[href],button,input,[tabindex="0"]')).filter(function (item) {return !item.disabled && item.getClientRects().length;});
        if (!items.length) return;
        var first=items[0],last=items[items.length-1];
        if (event.shiftKey && (document.activeElement===first || !activeDrawer.contains(document.activeElement))) {event.preventDefault();last.focus();}
        else if (!event.shiftKey && document.activeElement===last) {event.preventDefault();first.focus();}
    },true);

    document.addEventListener('scroll', function (event) {if (openMenu && !openMenu.menu.contains(event.target)) closeMenu();},true);
    document.addEventListener('show.bs.modal', closeDrawer);
    document.addEventListener('pms:records-updated', schedule);
    document.addEventListener('shown.bs.tab', schedule);
    document.addEventListener('shown.bs.modal', schedule);
    window.addEventListener('resize',function () {closeMenu();drawerState();schedule();});
    window.PMSMobileLayout = {scan:scan, closeDrawer:closeDrawer};
    function init() {
        scan();drawerState();
        new MutationObserver(schedule).observe(document.body,{childList:true,subtree:true});
        var drawers=new MutationObserver(drawerState);
        [document.body,document.getElementById('layout-menu'),document.getElementById('sidebar'),document.getElementById('devSidebar')].filter(Boolean).forEach(function (node) {drawers.observe(node,{attributes:true,attributeFilter:['class']});});
        new MutationObserver(drawerState).observe(document.documentElement,{attributes:true,attributeFilter:['class']});
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',init,{once:true});
    else init();
})();
