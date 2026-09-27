<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employee = App\Models\User::where('role', 'employee')->first();
if ($employee) {
    echo "Updating orphaned leaves to point to active employee: {$employee->name} (ID: {$employee->id})\n";
    App\Models\Leave::whereNotIn('user_id', App\Models\User::pluck('id'))->update([
        'user_id' => $employee->id,
        'company_id' => $employee->company_id ?: 1,
    ]);
    App\Models\Leave::whereNull('company_id')->update([
        'company_id' => $employee->company_id ?: 1,
    ]);
}

echo "Leaves updated. Current leaves in DB:\n";
foreach (App\Models\Leave::with('user')->get() as $l) {
    echo "Leave ID: {$l->id}, User: {$l->user?->name} (ID: {$l->user_id}), Status: {$l->status}, Company: {$l->company_id}\n";
}
