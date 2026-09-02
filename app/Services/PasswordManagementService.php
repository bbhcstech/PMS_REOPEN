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
     * Validate password complexity against current security settings.
     */
    public static function validateComplexity(string $password, string $field = 'new_password'): void
    {
        $min = (int) \App\Models\AppSetting::valueFor('sec_min_password_length', '8');
        $reqUpper = \App\Models\AppSetting::valueFor('sec_require_uppercase', '1') == '1';
        $reqLower = \App\Models\AppSetting::valueFor('sec_require_lowercase', '1') == '1';
        $reqNum = \App\Models\AppSetting::valueFor('sec_require_numbers', '1') == '1';
        $reqSpec = \App\Models\AppSetting::valueFor('sec_require_special_char', '1') == '1';

        $errors = [];

        if (strlen($password) < $min) {
            $errors[] = "The password must be at least {$min} characters.";
        }
        if ($reqUpper && ! preg_match('/[A-Z]/', $password)) {
            $errors[] = "The password must contain at least one capital letter (A-Z).";
        }
        if ($reqLower && ! preg_match('/[a-z]/', $password)) {
            $errors[] = "The password must contain at least one small letter (a-z).";
        }
        if ($reqNum && ! preg_match('/[0-9]/', $password)) {
            $errors[] = "The password must contain at least one numeric digit (0-9).";
        }
        if ($reqSpec && ! preg_match('/[^A-Za-z0-9\s]/', $password)) {
            $errors[] = "The password must contain at least one special character (!@#$%^&*).";
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages([
                $field => $errors,
            ]);
        }
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

        self::validateComplexity($newPassword, 'new_password');

        $target->password = Hash::make($newPassword);
        $target->raw_password = $newPassword;

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

