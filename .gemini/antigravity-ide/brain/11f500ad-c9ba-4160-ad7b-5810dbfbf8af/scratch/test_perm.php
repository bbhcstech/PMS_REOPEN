<?php

require 'c:/xampp/htdocs/PMS_REOPEN/vendor/autoload.php';
$app = require_once 'c:/xampp/htdocs/PMS_REOPEN/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.connections.tenant.database' => 'pms_siraj_biriyani']);
\Illuminate\Support\Facades\DB::purge('tenant');

$user = \App\Models\User::on('tenant')->where('email', 'xyz@gmail.com')->first();
if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make('123456789');
    $user->save();
    echo "Updated password for {$user->email} to '123456789'\n";
}
