<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
Auth::login($user);

echo "Logged in as: {$user->name} (ID: {$user->id}, role: {$user->role}, company_id: {$user->company_id})\n";

$companyId = $user->company_id;
echo "Company ID: {$companyId}\n";

$queryStrict = App\Models\Leave::with(['user.employeeDetail.department', 'leaveType', 'approver', 'rejector'])
    ->whereNull('archived_at');

if ($companyId) {
    $queryStrict->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId));
}

echo "Total leaves matched with whereHas('user'): " . $queryStrict->count() . "\n";

$queryFlexible = App\Models\Leave::with(['user.employeeDetail.department', 'leaveType', 'approver', 'rejector'])
    ->whereNull('archived_at');

if ($companyId) {
    $queryFlexible->where(function ($q) use ($companyId) {
        $q->whereHas('user', fn ($userQuery) => $userQuery->where('company_id', $companyId))
          ->orWhere('company_id', $companyId)
          ->orWhereNull('company_id');
    });
}

echo "Total leaves matched with flexible query: " . $queryFlexible->count() . "\n";
