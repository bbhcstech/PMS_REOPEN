<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangePasswordSettingsController extends Controller
{
    public function index()
    {
        $actor = Auth::user();

        // Exclude logged-in actor's own account from staff reset list
        $query = User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'user'])
            ->where('id', '!=', $actor->id);

        $actorRole = strtolower($actor->role ?? '');
        if (in_array($actorRole, ['hr', 'manager'], true)) {
            $query->whereIn('role', ['employee', 'user']);
        }

        $staffUsers = $query->orderBy('name')->get(['id', 'name', 'email', 'role', 'created_at', 'password_changed_at', 'password_changed_by_role']);

        $totalCount = $staffUsers->count();
        $hrCount = $staffUsers->filter(fn($u) => strtolower($u->role) === 'hr')->count();
        $managerCount = $staffUsers->filter(fn($u) => strtolower($u->role) === 'manager')->count();
        $employeeCount = $staffUsers->filter(fn($u) => in_array(strtolower($u->role), ['employee', 'user']))->count();

        return view('admin.settings.change-password', compact(
            'staffUsers',
            'totalCount',
            'hrCount',
            'managerCount',
            'employeeCount'
        ));
    }

    public function update(Request $request)
    {
        $actor = Auth::user();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        if ($targetUser->id === $actor->id) {
            return back()->with('error', 'To change your own password, please use the "Change My Own Password" section above.');
        }

        if (! PasswordManagementService::canChangePassword($actor, $targetUser)) {
            return back()->with('error', 'You do not have permission to change the password for this account.');
        }

        PasswordManagementService::updatePassword($actor, $targetUser, $request->new_password);

        return back()->with('success', "Password for {$targetUser->name} ({$targetUser->email}) updated successfully! The user will be notified and logged out.");
    }
}
