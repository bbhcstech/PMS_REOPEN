<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cookie, DB};

class AuthenticationSession
{
    public function clear(Request $request, bool $revokeRememberTokens = false): void
    {
        // Revoke tokens while the user's original tenant connection is still active.
        foreach (['web', 'super_admin'] as $name) {
            $guard = Auth::guard($name);
            if ($revokeRememberTokens) $guard->logout();
            Cookie::queue(Cookie::forget($guard->getRecallerName()));
            $guard->forgetUser();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::shouldUse('web');

        // mysql is mutable; session_db always refers to the original login database.
        $database = DB::connection('session_db')->getDatabaseName();
        config([
            'database.connections.tenant.database' => $database,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.url' => null,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');
        app(CompanyContext::class)->reset();
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->clear($request, true);
        return redirect()->route('login')
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
