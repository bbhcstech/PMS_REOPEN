<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RoleAccountController extends Controller
{
    public function index(string $role): View
    {
        $this->authorizeAdmin();
        $role = $this->normalizeManagedRole($role);

        return view('admin.settings.role-accounts.index', [
            'role' => $role,
            'title' => ucfirst($role) . ' Management',
            'accounts' => User::where('role', $role)->orderBy('name')->get(),
            'companies' => Company::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $role): RedirectResponse
    {
        $this->authorizeAdmin();
        $role = $this->normalizeManagedRole($role);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'company_id' => ['required', 'exists:companies,id'],
            'password' => ['required', 'string', 'min:8'],
            'login_allowed' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $data['name'],
            'company_id' => $data['company_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'login_allowed' => $request->boolean('login_allowed'),
            'is_active' => $request->boolean('login_allowed'),
        ]);

        return back()->with('success', ucfirst($role) . ' account created successfully.');
    }

    public function update(Request $request, string $role, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $role = $this->normalizeManagedRole($role);
        abort_unless($user->normalizedRole() === $role, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'company_id' => ['required', 'exists:companies,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'login_allowed' => ['nullable', 'boolean'],
        ]);

        $user->fill([
            'name' => $data['name'],
            'company_id' => $data['company_id'],
            'email' => $data['email'],
            'login_allowed' => $request->boolean('login_allowed'),
            'is_active' => $request->boolean('login_allowed'),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', ucfirst($role) . ' account updated successfully.');
    }

    public function resetPassword(Request $request, string $role, User $user): RedirectResponse
    {
        $this->authorizeAdmin();
        $role = $this->normalizeManagedRole($role);
        abort_unless($user->normalizedRole() === $role, 404);

        $data = $request->validate(['password' => ['required', 'string', 'min:8']]);
        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', ucfirst($role) . ' password reset successfully.');
    }

    private function normalizeManagedRole(string $role): string
    {
        $role = strtolower($role);
        abort_unless(in_array($role, ['hr', 'manager'], true), 404);

        return $role;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->normalizedRole() === 'admin', 403);
    }
}
