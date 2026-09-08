<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\CentralNotification;
use App\Models\Central\Company;
use App\Services\SubscriptionNotificationEngine;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificationCenterController extends Controller
{
    protected SubscriptionNotificationEngine $subscriptionEngine;

    public function __construct(SubscriptionNotificationEngine $subscriptionEngine)
    {
        $this->subscriptionEngine = $subscriptionEngine;
    }

    private function authorizeSuperAdmin(): void
    {
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $role = strtolower((string) ($user->role ?? ''));
            if ($role === 'superadmin' || $role === 'admin') {
                return;
            }
        }

        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.', ['web', 'super_admin']);
    }

    /**
     * Notification & Alert Center Page.
     * Delegates to CompanyController::alerts() to reuse the full alert-building logic.
     */
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();

        // Run automated subscription scan
        $this->subscriptionEngine->scanAndGenerateAlerts();

        // Delegate to the unified alert-building logic in CompanyController
        $companyController = app(\App\Http\Controllers\SuperAdmin\CompanyController::class);
        return $companyController->alerts($request, 'superadmin.notifications.index');
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead($id): JsonResponse|RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $notification = CentralNotification::on('central')->findOrFail($id);
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
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse|RedirectResponse
    {
        $this->authorizeSuperAdmin();

        CentralNotification::on('central')
            ->where(function ($q) {
                $q->where('target_audience', 'super_admin')->orWhere('target_audience', 'all');
            })
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
     * Unread count API for top header bell & sidebar.
     */
    public function unreadCount(): JsonResponse
    {
        $count = CentralNotification::on('central')
            ->where(function ($q) {
                $q->where('target_audience', 'super_admin')->orWhere('target_audience', 'all');
            })
            ->where('is_read', false)
            ->count();

        $latest = CentralNotification::on('central')
            ->where(function ($q) {
                $q->where('target_audience', 'super_admin')->orWhere('target_audience', 'all');
            })
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'count'  => $count,
            'latest' => $latest,
        ]);
    }
}
