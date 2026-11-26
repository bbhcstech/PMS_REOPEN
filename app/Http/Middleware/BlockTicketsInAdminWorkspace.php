<?php

namespace App\Http\Middleware;

use App\Support\TicketAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Internal tickets require a company workspace; platform support stays separate.
 */
class BlockTicketsInAdminWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TicketAccess::isTicketRoute($request->route()?->getName()) || ! TicketAccess::hiddenForCurrentUser()) {
            return $next($request);
        }

        $message = 'Internal tickets require a company workspace.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
