<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\CentralNotification;
use App\Models\Central\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CompanyNotificationController extends Controller
{
    private function getActiveCompanyId(): int
    {
        // Company platform alerts are restricted to company administrators.
        if (! in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator', 'superadmin'], true)) {
            abort(403, 'Unauthorized: Only company administrators can access company notifications.');
        }

        $contextComp = app(\App\Services\CompanyContext::class)->current();
        if ($contextComp) {
            \App\Services\TenantScope::authorizeCompany($contextComp->id);
            return (int) $contextComp->id;
        }

        $user = auth()->user();
        if ($user && $user->company_id) {
            return (int) $user->company_id;
        }

        abort(403, 'Unauthorized: Company context could not be identified.');
    }

    /**
     * Company Admin Notification Center.
     */
    public function index(Request $request): View
    {
        $companyId = $this->getActiveCompanyId();

        try {
            app(\App\Services\SubscriptionNotificationEngine::class)->scanAndGenerateAlerts($companyId);
        } catch (\Throwable $e) {}

        $query = CentralNotification::on('central')
            ->with(['company.subscriptions.plan'])
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('target_audience', 'company_admin')
                  ->orWhere('target_audience', 'all');
            });

        if ($request->filled('severity')) {
            $query->where('severity', strtoupper($request->severity));
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $baseQuery = CentralNotification::on('central')
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('target_audience', 'company_admin')
                  ->orWhere('target_audience', 'all');
            });

        $kpis = [
            'total'    => (clone $baseQuery)->count(),
            'unread'   => (clone $baseQuery)->where('is_read', false)->count(),
            'critical' => (clone $baseQuery)->where('severity', 'CRITICAL')->where('is_read', false)->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'kpis'));
    }

    /**
     * Mark single notification read.
     */
    public function markAsRead($id): JsonResponse|RedirectResponse
    {
        $companyId = $this->getActiveCompanyId();

        $notification = CentralNotification::on('central')
            ->where('company_id', $companyId)
            ->whereIn('target_audience', ['company_admin', 'all'])
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications read.
     */
    public function markAllRead(): JsonResponse|RedirectResponse
    {
        $companyId = $this->getActiveCompanyId();

        CentralNotification::on('central')
            ->where('company_id', $companyId)
            ->whereIn('target_audience', ['company_admin', 'all'])
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Unread count API for Company Admin header.
     */
    public function unreadCount(): JsonResponse
    {
        $companyId = $this->getActiveCompanyId();

        $count = CentralNotification::on('central')
            ->where('company_id', $companyId)
            ->whereIn('target_audience', ['company_admin', 'all'])
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
