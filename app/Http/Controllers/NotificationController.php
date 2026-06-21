<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationUrlResolver;
use App\Services\SidebarNotificationService;
use App\Services\SystemNotificationService;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display all notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // 🔥 CHANGE THIS: Use ONE view file instead of two
        return view('admin.notifications.index', compact('notifications'));
    }


    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if (! request()->expectsJson() && ! request()->ajax()) {
            $url = request('redirect_url') ?: ($notification ? NotificationUrlResolver::resolve($notification) : null);
            return $url ? redirect($url) : back();
        }

        return response()->json(['status' => 'ok']);
    }

    public function open($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return redirect(NotificationUrlResolver::resolve($notification));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        if (! request()->expectsJson() && ! request()->ajax()) {
            return back()->with('success', 'All notifications marked as read.');
        }

        return response()->json(['status' => 'ok']);
    }

    public function markSectionAsRead(string $section)
    {
        $section = strtolower($section);
        $marked = 0;

        foreach (auth()->user()->unreadNotifications as $notification) {
            if ($this->notificationBelongsToSection($notification, $section)) {
                $notification->markAsRead();
                $marked++;
            }
        }

        return response()->json([
            'status' => 'ok',
            'marked' => $marked,
            'count' => auth()->user()->unreadNotifications()->count(),
            'items' => SidebarNotificationService::forUser(auth()->user()),
        ]);
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function latest()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (DatabaseNotification $notification) => [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
                'created_at' => optional($notification->created_at)->diffForHumans(),
                'data' => $notification->data,
                'open_url' => route('notifications.open', $notification->id),
                'target_url' => NotificationUrlResolver::resolve($notification),
            ]);

        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function sidebar()
    {
        return response()->json([
            'items' => SidebarNotificationService::forUser(auth()->user()),
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'All notifications cleared');
    }

    /**
     * Send notification from Admin to ALL Employees
     */
    public function adminToEmployees(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'url' => $request->url,
            'ticket_id' => $request->ticket_id,
            'type' => 'admin_to_employee',
            'icon' => 'fa-shield-halved',
            'color' => 'warning',
        ];

        SystemNotificationService::notifyAllRoles($request->title, $request->message, $request->url, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to all ERP roles',
            'count' => SystemNotificationService::roleUsers(auth()->user()?->company_id)->count()
        ]);
    }

    /**
     * Send notification from Employee to ALL Admins
     */
    public function employeeToAdmins(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'url' => $request->url,
            'ticket_id' => $request->ticket_id,
            'type' => 'employee_to_admin',
            'icon' => 'fa-user-clock',
            'color' => 'info',
        ];

        SystemNotificationService::notifyAllRoles($request->title, $request->message, $request->url, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to all ERP roles',
            'count' => SystemNotificationService::roleUsers(auth()->user()?->company_id)->count()
        ]);
    }

    /**
     * Send notification to specific users
     */
    public function sendToUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $sender = auth()->user();
        SystemNotificationService::notifyAllRoles($request->title, $request->message, $request->url, [
            'type' => 'manual_notification',
            'ticket_id' => $request->ticket_id,
            'sender_id' => $sender?->id,
            'sender_name' => $sender?->name,
            'sender_role' => $sender?->role,
            'icon' => 'fa-envelope',
            'color' => 'primary',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to all ERP roles',
            'count' => SystemNotificationService::roleUsers(auth()->user()?->company_id)->count()
        ]);
    }

    /**
     * Delete single notification
     */
    public function delete($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        return response()->json(['status' => 'ok']);
    }

    private function notificationBelongsToSection(DatabaseNotification $notification, string $section): bool
    {
        if ($section === 'notifications') {
            return true;
        }

        $data = $notification->data ?? [];
        $type = strtolower((string) data_get($data, 'type', ''));
        $url = strtolower((string) data_get($data, 'url', ''));

        $aggregateMap = [
            'hr' => ['employees', 'attendance', 'leaves', 'holidays'],
            'work' => ['projects', 'tasks', 'timelogs', 'tickets', 'clients'],
            'reports' => ['attendance', 'leaves', 'timelogs'],
        ];

        if (isset($aggregateMap[$section])) {
            foreach ($aggregateMap[$section] as $childSection) {
                if ($this->notificationBelongsToSection($notification, $childSection)) {
                    return true;
                }
            }

            return false;
        }

        $idKeys = [
            'employees' => ['employee_id', 'user_id'],
            'attendance' => ['attendance_id', 'record_id'],
            'leaves' => ['leave_id', 'apology_letter_id', 'leave_apology_letter_id'],
            'holidays' => ['holiday_id'],
            'projects' => ['project_id'],
            'tasks' => ['task_id'],
            'timelogs' => ['timelog_id', 'timer_id'],
            'tickets' => ['ticket_id'],
            'clients' => ['client_id'],
        ];

        foreach ($idKeys[$section] ?? [] as $key) {
            if (data_get($data, $key)) {
                return true;
            }
        }

        $keywords = [
            'employees' => ['employee', 'profile', 'user'],
            'attendance' => ['attendance', 'clock_in', 'clockin', 'clock-out'],
            'leaves' => ['leave', 'apology'],
            'holidays' => ['holiday'],
            'projects' => ['project'],
            'tasks' => ['task'],
            'timelogs' => ['timelog', 'timer', 'timesheet'],
            'tickets' => ['ticket'],
            'clients' => ['client'],
            'reports' => ['report'],
        ];

        foreach ($keywords[$section] ?? [] as $needle) {
            if (str_contains($type, $needle) || str_contains($url, $needle)) {
                return true;
            }
        }

        return false;
    }

}
