<style>
    #moduleManagementPage .module-category td { background:#f1f5f9; color:#475569; font-size:11px; letter-spacing:.06em; padding:14px 20px; }
    #moduleManagementPage .module-category span { font-weight:400; letter-spacing:0; }
    #moduleManagementPage tr[hidden] { display:none; }
    .role-permission-page { background:#f8fafc; }
    .role-permission-page .ambient-orb { display:none; }
    .role-permission-page .branches-header, .role-permission-page .stat-card, .role-permission-page .role-select-card { border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(15,23,42,.04); border-radius:16px; }
    .role-permission-page .matrix-table tbody .permission-category td { background:#f1f5f9; color:#475569; font-size:11px; font-weight:700; letter-spacing:.06em; padding:12px 20px; }
    .permission-category small { font-weight:400; letter-spacing:0; }
    .role-permission-page .matrix-table tbody tr[hidden] { display:none; }
    .role-permission-page .matrix-table .perm-checkbox:focus-visible { outline:3px solid #93c5fd; outline-offset:3px; }
    @media (prefers-reduced-motion:reduce) { .role-permission-page *, #moduleManagementPage * { animation:none!important; transition:none!important; } }
    .access-navigation { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; padding:14px 18px; margin-bottom:24px; border:1px solid #e2e8f0; border-radius:16px; background:#fff; box-shadow:0 4px 16px rgba(15,23,42,.04); }
    .access-tabs { display:flex; gap:6px; flex-wrap:wrap; }
    .access-tabs a { padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px; color:#475569; }
    .access-tabs a[aria-current="page"] { background:#eff6ff; color:#2563eb; }
    .access-company { font-size:12px; color:#475569; display:flex; align-items:center; gap:8px; }
    .access-company i { color:#16a34a; }
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) .access-navigation { background:#1e293b; border-color:#334155; }
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) .access-company { color:#cbd5e1; }
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) .access-tabs a { color:#cbd5e1; }
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) .access-tabs a[aria-current="page"] { background:#1e3a5f; color:#93c5fd; }
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) #moduleManagementPage .module-category td,
    :is(html[data-pms-theme="dark"], html[data-theme="dark"], html[data-bs-theme="dark"], .dark) .role-permission-page .matrix-table tbody .permission-category td { background:#1e293b; color:#cbd5e1; }
</style>
<nav class="access-navigation" aria-label="Company access settings">
    <div class="access-tabs">
        <a href="{{ route('admin.modules.index') }}" @if(request()->routeIs('admin.modules.*')) aria-current="page" @endif><i class="bx bx-grid-alt me-1"></i>Module Settings</a>
        <a href="{{ route('admin.role-permissions.index') }}" @if(request()->routeIs('admin.role-permissions.*')) aria-current="page" @endif><i class="bx bx-shield-quarter me-1"></i>Roles & Permissions</a>
    </div>
    <span class="access-company"><i class="bx bx-lock-alt"></i>{{ session('current_company_name', 'Company workspace') }} · Company access settings</span>
</nav>
