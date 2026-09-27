<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = ['admin', 'superadmin', 'hr', 'employee', 'manager'];
foreach ($roles as $role) {
    $user = App\Models\User::where('role', $role)->first();
    if (!$user) continue;

    Auth::login($user);
    $controller = new App\Http\Controllers\LeaveController(app(App\Services\LeaveService::class));
    $request = new Illuminate\Http\Request();
    
    // Call index method or simulate it
    $isAdmin = in_array(strtolower((string) $user->role), ['admin', 'superadmin', 'hr', 'manager'], true);
    $companyId = $request->integer('company_id') ?: ($isAdmin ? null : $user->company_id);

    $query = App\Models\Leave::with(['user.employeeDetail.department', 'leaveType', 'approver', 'rejector'])
        ->whereNull('archived_at');

    if (! $isAdmin) {
        $query->where('user_id', $user->id);
    } elseif ($companyId) {
        $query->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
    }

    $leavesCount = $query->count();
    echo "Role: {$role} (User ID: {$user->id}, Company ID: " . var_export($user->company_id, true) . ") => Leaves count: {$leavesCount}\n";
}
