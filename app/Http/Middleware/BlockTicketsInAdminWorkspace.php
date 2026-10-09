<?php

namespace App\Http\Middleware;

use App\Support\TicketAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the ticket system out of the Admin Workspace, where
 * "Platform Support & Complaints" is the single support channel.
 */
class BlockTicketsInAdminWorkspace
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TicketAccess::isTicketRoute($request->route()?->getName()) || ! TicketAccess::hiddenForCurrentUser()) {
            return $next($request);
        }

        $message = 'Tickets are not part of the Admin Workspace. Please use Platform Support & Complaints.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('admin.company-complaints.index')->with('info', $message);
    }
}
