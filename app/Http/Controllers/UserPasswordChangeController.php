<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PasswordManagementService;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserPasswordChangeController extends Controller
{
    /**
     * Change logged-in user's own password.
     * Dispatches notification to ONLY Admin dashboard (view-only/non-clickable).
     */
    public function changeOwnPassword(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed'],
        ]);

        PasswordManagementService::validateComplexity($request->new_password, 'new_password');

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->raw_password = $request->new_password;
        $user->password_changed_at = now();
        $user->save();

        $roleLabel = match (strtolower($user->role ?? '')) {
            'hr' => 'HR',
            'manager' => 'Manager',
            'employee', 'user' => 'Employee',
            default => ucfirst($user->role ?? 'User'),
        };

        // Notify ONLY Admins with non-clickable notification
        SystemNotificationService::send(
            SystemNotificationService::admins($user->company_id),
            'Password Changed: ' . $user->name,
            "{$user->name} ({$roleLabel}) has updated their own account password.",
            null,
            [
                'type' => 'own_password_changed',
                'clickable' => false,
                'icon' => 'fa-key',
                'color' => 'warning',
                'actor_id' => $user->id,
                'actor_name' => $user->name,
                'actor_role' => $user->role,
            ]
        );

        return back()->with('success', 'Your password has been changed successfully.');
    }
    /**
     * Change a staff member's password.
     * Admin: can change HR, Manager, Employee passwords.
     * HR & Manager: can change Employee passwords.
     */
    public function changePassword(Request $request, User $user)
    {
        $actor = Auth::user();

        if (! PasswordManagementService::canChangePassword($actor, $user)) {
            return back()->with('error', 'You do not have permission to change the password for this user.');
        }

        $request->validate([
            'new_password' => 'required|string|confirmed',
        ]);

        PasswordManagementService::updatePassword($actor, $user, $request->new_password);

        return back()->with('success', "Password for {$user->name} has been updated successfully. They will be notified and logged out.");
    }

    /**
     * Handle immediate logout when user acknowledges password change or timer elapses.
     */
    public function passwordChangedLogout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->password_changed_notice = false;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out because your password was changed. Please log in with your new password.');
    }

    /**
     * Polling endpoint to check if password was changed while sitting on an open page.
     */
    public function checkPasswordStatus()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['changed' => false]);
        }

        return response()->json([
            'changed' => (bool) $user->password_changed_notice,
            'by_role' => $user->password_changed_by_role ?? 'Admin',
        ]);
    }
}
