<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        $roleLabel = match (strtolower($user->role ?? '')) {
            'hr' => 'HR',
            'manager' => 'Manager',
            'employee', 'user' => 'Employee',
            default => ucfirst($user->role ?? 'User'),
        };

        // Send non-clickable notification to Admin ONLY
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

        return back()->with('status', 'password-updated');
    }
}
