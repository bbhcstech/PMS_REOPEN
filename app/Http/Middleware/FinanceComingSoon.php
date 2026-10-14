<?php

namespace App\Http\Middleware;

use App\Services\TenantScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Finance features (finance / income-vs-expense / expense reports and project expenses) are not
 * released yet: every company role sees a "Coming soon" page and no finance data can be changed.
 */
class FinanceComingSoon
{
    public const ROUTES = [
        'reports.finance' => 'Finance Report',
        'reports.income-vs-expense' => 'Income Vs Expense',
        'reports.expense' => 'Expense Report',
        'expenses.*' => 'Project Expenses',
    ];

    public const MESSAGE = 'This finance section is coming soon. It is not developed yet.';

    public static function sectionFor(?string $routeName): ?string
    {
        if (! $routeName) return null;
        foreach (self::ROUTES as $pattern => $label) {
            if (\Illuminate\Support\Str::is($pattern, $routeName)) return $label;
        }
        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $section = self::sectionFor($request->route()?->getName());
        if (! $section || ! $request->user() || TenantScope::isPlatformAdmin()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => self::MESSAGE, 'coming_soon' => true], 403);
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return redirect()->back()->with('info', self::MESSAGE);
        }

        return response()->view('admin.coming-soon', ['section' => $section, 'message' => self::MESSAGE]);
    }
}
