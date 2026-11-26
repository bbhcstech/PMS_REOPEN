<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$leaves = App\Models\Leave::all();
echo "Total leaves in DB: " . $leaves->count() . "\n";
foreach ($leaves as $l) {
    echo "ID: {$l->id}, user_id: {$l->user_id}, company_id: {$l->company_id}, status: {$l->status}, archived_at: {$l->archived_at}\n";
}

$users = App\Models\User::all();
echo "Total users in DB: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "User ID: {$u->id}, Name: {$u->name}, Role: {$u->role}, Company ID: {$u->company_id}\n";
}
