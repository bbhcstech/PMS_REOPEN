<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordManagementService
{
    /**
     * Check whether actor is permitted to change target user's password.
     */
    public static function canChangePassword(User $actor, User $target): bool
    {
        $actorRole = strtolower($actor->role ?? '');
        $targetRole = strtolower($target->role ?? '');

        // Self password change is allowed
        if ($actor->id === $target->id) {
            return true;
        }

        // Admin can change password for HR, Manager, and Employee (and other admins)
        if ($actorRole === 'admin') {
            return in_array($targetRole, ['admin', 'hr', 'manager', 'employee', 'user'], true);
        }

        // HR and Manager can change password ONLY for Employees
        if (in_array($actorRole, ['hr', 'manager'], true)) {
            return $targetRole === 'employee' || $targetRole === 'user';
        }

        return false;
    }

    /**
     * Change user password and set target user notice & force logout flags if changed by another staff member.
     */
    public static function updatePassword(User $actor, User $target, string $newPassword): void
    {
        if (! self::canChangePassword($actor, $target)) {
            throw ValidationException::withMessages([
                'password' => ['You do not have permission to change password for this user.'],
            ]);
        }

        $target->password = Hash::make($newPassword);

        // If changed by someone else (Admin, HR, or Manager)
        if ($actor->id !== $target->id) {
            $roleLabel = match (strtolower($actor->role ?? '')) {
                'admin' => 'Admin',
                'hr' => 'HR',
                'manager' => 'Manager',
                default => ucfirst($actor->role ?? 'Admin'),
            };

            $target->password_changed_notice = true;
            $target->password_changed_by_role = $roleLabel;
            $target->password_changed_at = now();
        }

        $target->save();
    }
}
