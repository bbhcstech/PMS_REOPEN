<?php

// Run with: php tests/regression/products_and_orders_server_test.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Models\Central\Company as CentralCompany;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Setup in-memory sqlite database for testing
foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.{$connection}" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);

// Create central tables
Schema::connection('central')->create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('status')->default('active');
    $table->string('email')->nullable();
    $table->string('company_code')->nullable();
    $table->string('db_name')->nullable();
    $table->softDeletes();
    $table->timestamps();
});

Schema::connection('central')->create('modules', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('icon')->nullable();
    $table->text('description')->nullable();
    $table->string('route_name')->nullable();
    $table->string('route_prefix')->nullable();
    $table->boolean('is_core')->default(false);
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

Schema::connection('central')->create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->decimal('price', 8, 2)->default(0);
    $table->timestamps();
});

Schema::connection('central')->create('plan_modules', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('plan_id');
    $table->unsignedBigInteger('module_id');
    $table->timestamps();
});

Schema::connection('central')->create('company_modules', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->unsignedBigInteger('module_id');
    $table->boolean('is_enabled')->default(true);
    $table->timestamps();
});

Schema::connection('central')->create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->unsignedBigInteger('plan_id');
    $table->string('status')->default('active');
    $table->timestamps();
});

// Create tenant tables
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('status')->default('active');
    $table->timestamps();
});

Schema::create('modules', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('icon')->nullable();
    $table->text('description')->nullable();
    $table->string('route_prefix')->nullable();
    $table->boolean('is_core')->default(false);
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

Schema::create('role_permissions', function (Blueprint $table) {
    $table->id();
    $table->string('role');
    $table->unsignedBigInteger('module_id');
    $table->boolean('can_view')->default(false);
    $table->boolean('can_create')->default(false);
    $table->boolean('can_edit')->default(false);
    $table->boolean('can_delete')->default(false);
    $table->boolean('can_approve')->default(false);
    $table->boolean('can_export')->default(false);
    $table->boolean('can_assign')->default(false);
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('name')->default('Test User');
    $table->string('email')->unique();
    $table->string('role')->default('employee');
    $table->string('password')->default('secret');
    $table->timestamps();
});

Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('name');
    $table->string('company_name')->nullable();
    $table->string('email')->nullable();
    $table->boolean('login_allowed')->default(true);
    $table->timestamps();
});

Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->unsignedBigInteger('client_id')->nullable();
    $table->string('name');
    $table->string('project_code')->nullable();
    $table->string('project_type')->default('client');
    $table->string('status')->default('in progress');
    $table->string('payment_status')->nullable()->default('unpaid');
    $table->string('priority')->default('medium');
    $table->decimal('project_budget', 12, 2)->nullable();
    $table->text('description')->nullable();
    $table->integer('completion_percent')->default(0);
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('deals', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('deal_name')->nullable();
    $table->string('product')->nullable();
    $table->decimal('value', 12, 2)->default(0);
    $table->string('lead_name')->nullable();
    $table->string('contact_details')->nullable();
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('deal_stages', function (Blueprint $table) {
    $table->id();
    $table->string('name')->default('Won');
    $table->timestamps();
});

Schema::create('deal_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->default('Standard');
    $table->timestamps();
});

Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->unsignedBigInteger('project_id')->nullable();
    $table->string('title')->default('Task 1');
    $table->unsignedBigInteger('assigned_to')->nullable();
    $table->unsignedBigInteger('category_id')->nullable();
    $table->string('status')->default('in progress');
    $table->integer('progress')->default(0);
    $table->timestamps();
});

Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id')->nullable();
    $table->decimal('price', 12, 2)->default(0);
    $table->timestamps();
});

Schema::create('project_milestones', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id')->nullable();
    $table->string('milestone_title')->default('M1');
    $table->timestamps();
});

Schema::create('project_updates', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id')->nullable();
    $table->timestamps();
});

Schema::create('project_user', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('project_id');
    $table->unsignedBigInteger('user_id');
    $table->decimal('hourly_rate', 8, 2)->nullable();
    $table->string('role')->nullable();
    $table->unsignedBigInteger('assigned_by')->nullable();
    $table->timestamp('assigned_at')->nullable();
    $table->timestamps();
});

Schema::create('employee_details', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->timestamps();
});

// Run the migration class directly to test it
$migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_08_000002_ensure_products_and_orders_modules_and_permissions.php';
$migration->up();

echo "Running Products & Orders Server Parity Tests...\n";

// Test 1: Verify migration inserted records in central modules
$centralProductsMod = DB::connection('central')->table('modules')->where('slug', 'products')->first();
$centralOrdersMod = DB::connection('central')->table('modules')->where('slug', 'orders')->first();
assert($centralProductsMod !== null, "Central modules table must contain 'products'");
assert($centralOrdersMod !== null, "Central modules table must contain 'orders'");
echo "[PASS] Test 1: Migration seeded central modules successfully\n";

// Test 2: Verify migration inserted records in tenant modules and role_permissions
$tenantProductsMod = DB::table('modules')->where('slug', 'products')->first();
$tenantOrdersMod = DB::table('modules')->where('slug', 'orders')->first();
assert($tenantProductsMod !== null, "Tenant modules table must contain 'products'");
assert($tenantOrdersMod !== null, "Tenant modules table must contain 'orders'");

$adminPerm = DB::table('role_permissions')->where('role', 'admin')->where('module_id', $tenantProductsMod->id)->first();
assert($adminPerm !== null && $adminPerm->can_view == 1, "Admin role_permissions for products must exist and can_view == 1");

$empPerm = DB::table('role_permissions')->where('role', 'employee')->where('module_id', $tenantOrdersMod->id)->first();
assert($empPerm !== null && $empPerm->can_view == 1, "Employee role_permissions for orders must exist and can_view == 1");
echo "[PASS] Test 2: Migration seeded tenant modules and role_permissions successfully\n";

// Test 3: Test Company hasFeature('products') and hasFeature('orders')
$company = Company::create(['name' => 'Acme Corp', 'status' => 'active']);
CentralCompany::create(['id' => $company->id, 'name' => 'Acme Corp', 'status' => 'active']);

assert($company->hasFeature('products') === true, "Company::hasFeature('products') should be true");
assert($company->hasFeature('orders') === true, "Company::hasFeature('orders') should be true");
echo "[PASS] Test 3: Company hasFeature returns true for active company\n";

// Test 4: Test with active subscription plan containing specific plan_modules (e.g. only 'work' or 'projects')
$plan = DB::connection('central')->table('plans')->insertGetId(['name' => 'Starter Plan', 'slug' => 'starter', 'price' => 29.00]);
$projectsCentralMod = DB::connection('central')->table('modules')->insertGetId([
    'name' => 'Projects', 'slug' => 'projects', 'is_core' => 1, 'is_active' => 1
]);
DB::connection('central')->table('plan_modules')->insert([
    ['plan_id' => $plan, 'module_id' => $projectsCentralMod],
]);
DB::connection('central')->table('subscriptions')->insert([
    'company_id' => $company->id, 'plan_id' => $plan, 'status' => 'active'
]);

assert($company->hasFeature('products') === true, "Company::hasFeature('products') must be true under subscription plan with projects/work");
assert($company->hasFeature('orders') === true, "Company::hasFeature('orders') must be true under subscription plan with projects/work");
echo "[PASS] Test 4: Company hasFeature returns true under plan_modules restriction\n";

// Test 5: Test User canViewModule for all roles
$adminUser = User::create(['company_id' => $company->id, 'name' => 'Admin User', 'email' => 'admin@test.com', 'role' => 'admin']);
$managerUser = User::create(['company_id' => $company->id, 'name' => 'Manager User', 'email' => 'manager@test.com', 'role' => 'manager']);
$hrUser = User::create(['company_id' => $company->id, 'name' => 'HR User', 'email' => 'hr@test.com', 'role' => 'hr']);
$employeeUser = User::create(['company_id' => $company->id, 'name' => 'Emp User', 'email' => 'emp@test.com', 'role' => 'employee']);

Auth::login($adminUser);
assert($adminUser->canViewModule('products') === true, "Admin canViewModule('products') should be true");
assert($adminUser->canViewModule('orders') === true, "Admin canViewModule('orders') should be true");

assert($managerUser->canViewModule('products') === true, "Manager canViewModule('products') should be true");
assert($managerUser->canViewModule('orders') === true, "Manager canViewModule('orders') should be true");

assert($hrUser->canViewModule('products') === true, "HR canViewModule('products') should be true");
assert($hrUser->canViewModule('orders') === true, "HR canViewModule('orders') should be true");

assert($employeeUser->canViewModule('products') === true, "Employee canViewModule('products') should be true");
assert($employeeUser->canViewModule('orders') === true, "Employee canViewModule('orders') should be true");
echo "[PASS] Test 5: canViewModule returns true across all user roles\n";

// Test 6: Test ProductController and OrderController execute without exception
$client = \App\Models\Client::create(['company_id' => $company->id, 'name' => 'Globex Corp', 'email' => 'contact@globex.com']);
$project = Project::create([
    'company_id' => $company->id,
    'client_id' => $client->id,
    'name' => 'E-Commerce Redesign',
    'project_code' => 'PRJ-101',
    'project_type' => 'client',
    'status' => 'in progress',
    'payment_status' => 'unpaid',
    'project_budget' => 5000.00,
]);
Deal::create([
    'company_id' => $company->id,
    'deal_name' => 'E-Commerce Redesign Deal',
    'value' => 5000.00,
]);

$productController = new ProductController();
$productView = $productController->index(new Request());
assert($productView instanceof \Illuminate\View\View, "ProductController::index should return a View");
echo "[PASS] Test 6: ProductController::index executed successfully\n";

$orderController = new OrderController();
$orderView = $orderController->index(new Request());
assert($orderView instanceof \Illuminate\View\View, "OrderController::index should return a View");

$updateReq = new Request(['payment_status' => 'paid']);
$jsonRes = $orderController->updatePaymentStatus($updateReq, $project->id);
assert($jsonRes->getData()->success === true, "OrderController::updatePaymentStatus should return success = true");
$project->refresh();
assert($project->payment_status === 'paid', "Project payment_status should be updated to paid");
echo "[PASS] Test 7: OrderController executed and updated payment status successfully\n";

echo "\nAll Products & Orders Server Parity Tests Passed Successfully!\n";
