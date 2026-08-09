<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a list of all registered tenant companies.
     */
    public function index(): View
    {
        $companies = Company::on('central')->latest()->get();
        $currentCompanyDb = session('current_company_db');

        return view('superadmin.companies.index', compact('companies', 'currentCompanyDb'));
    }

    /**
     * Show form to create a new tenant company.
     */
    public function create(): View
    {
        return view('superadmin.companies.create');
    }

    /**
     * Store and provision a new tenant company database and default admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'slug'           => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'email'          => ['required', 'email', 'max:255', 'unique:central.companies,email'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'admin_name'     => ['required', 'string', 'max:255'],
            'admin_email'    => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $rawSlug = strtolower(trim($data['slug']));
        $slug = preg_replace('/[^a-z0-9_]/', '', $rawSlug);
        $dbName = 'pms_' . $slug;

        // Check if DB name or company code already exists in central registry
        $existing = Company::on('central')->where('db_name', $dbName)
            ->orWhere('company_code', strtoupper($slug))
            ->first();

        if ($existing) {
            return back()->withErrors([
                'slug' => "Company with database identifier '{$dbName}' already exists.",
            ])->withInput();
        }

        // 1. Create physical MySQL database matching utf8mb4_general_ci charset
        try {
            $pdo = DB::connection('central')->getPdo();
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        } catch (\Throwable $e) {
            return back()->withErrors([
                'error' => "Failed to create database '{$dbName}': " . $e->getMessage(),
            ])->withInput();
        }

        // 2. Register Company in central database
        $company = Company::on('central')->create([
            'company_code'   => strtoupper($slug),
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'db_name'        => $dbName,
            'status'         => 'active',
            'max_users'      => 100,
            'max_projects'   => 50,
            'max_clients'    => 100,
            'max_storage_mb' => 10000,
        ]);

        // 3. Configure tenant connection dynamically and run tenant migrations
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');

        $exitCode = Artisan::call('migrate', [
            '--path'     => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force'    => true,
        ]);

        if ($exitCode !== 0) {
            return back()->withErrors([
                'error' => "Migration failed for database '{$dbName}':\n" . Artisan::output(),
            ])->withInput();
        }

        // 4. Seed default admin user into the new tenant DB
        User::on('tenant')->create([
            'name'          => $data['admin_name'],
            'email'         => $data['admin_email'],
            'password'      => Hash::make($data['admin_password']),
            'company_id'    => null,
            'role'          => 'admin',
            'is_active'     => true,
            'login_allowed' => true,
        ]);

        return redirect()->route('super-admin.companies.index')
            ->with('success', "Tenant Company '{$company->name}' created successfully with database '{$dbName}'.");
    }

    /**
     * Enter (impersonate) a tenant company by setting session key.
     */
    public function enter(Company $company): RedirectResponse
    {
        session(['current_company_db' => $company->db_name]);

        return redirect('/dashboard')
            ->with('success', "Switched database context to tenant: {$company->name} ({$company->db_name})");
    }

    /**
     * Clear tenant session key and return to Super Admin panel.
     */
    public function leaveImpersonation(): RedirectResponse
    {
        session()->forget('current_company_db');

        return redirect()->route('super-admin.companies.index')
            ->with('success', 'Exited tenant company impersonation.');
    }
}
