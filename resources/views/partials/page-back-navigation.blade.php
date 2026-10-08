@auth
@php
    $backTarget = url()->previous();
    $backPath = parse_url($backTarget, PHP_URL_PATH) ?: '/';
    $safeBackTarget = false;
    if (parse_url($backTarget, PHP_URL_HOST) === request()->getHost()
        && in_array(parse_url($backTarget, PHP_URL_SCHEME), ['http', 'https'], true)
        && $backTarget !== url()->current()) {
        try {
            $backRoute = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($backTarget, 'GET'));
            $safeBackTarget = !preg_match('/(?:logout|signout|login|register)/i', $backRoute->getName() ?? $backPath);
        } catch (\Throwable $e) {
            $safeBackTarget = false;
        }
    }
    if (!$safeBackTarget) {
        $backRole = strtolower(trim((string) auth()->user()->role));
        $backDashboard = match ($backRole) {
            'developer' => 'developer.dashboard',
            'superadmin', 'super-admin', 'super_admin' => 'super-admin.dashboard',
            'hr' => 'hr.dashboard',
            'client' => 'dashboard.client',
            default => 'dashboard',
        };
        $backTarget = \Illuminate\Support\Facades\Route::has($backDashboard) ? route($backDashboard) : route('dashboard');
    }
@endphp
<nav class="pms-page-back-navigation" aria-label="Page navigation">
    <a href="{{ $backTarget }}" class="pms-page-back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m10 5-7 7 7 7M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Back</span>
    </a>
</nav>
<style>
html body .pms-page-back-navigation{position:relative;display:flex;align-items:center;clear:both;box-sizing:border-box;width:100%;padding:12px 16px;margin:0 0 12px;flex-shrink:0}
html body .pms-page-back-navigation .pms-page-back-link{position:static;display:inline-flex;align-items:center;gap:8px;min-height:44px;max-width:100%;padding:10px 16px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;color:#245be0;text-decoration:none;font-weight:700;line-height:1.4;box-sizing:border-box}
html body .pms-page-back-link span{color:inherit!important;-webkit-text-fill-color:currentColor!important}
html body .pms-page-back-link svg{background:transparent!important;stroke:currentColor!important;flex-shrink:0}
html body .pms-page-back-link:focus-visible{outline:3px solid #60a5fa;outline-offset:3px}
:is([data-pms-theme="dark"],[data-bs-theme="dark"],[data-theme="dark"],.dark,.dark-mode) .pms-page-back-navigation .pms-page-back-link{background:#141b35!important;border-color:#334264!important;color:#93c5fd!important}
</style>
@endauth
