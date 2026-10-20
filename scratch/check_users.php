<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$roles = User::select('role')->distinct()->get()->pluck('role');
echo "Distinct User Roles in DB:\n";
print_r($roles->toArray());

$users = User::select('id', 'name', 'email', 'role')->get();
echo "\nSample Users:\n";
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
}
