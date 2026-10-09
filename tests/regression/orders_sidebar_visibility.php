<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

$sidebar = file_get_contents(resource_path('views/admin/layout/manu.blade.php'));
$start = strpos($sidebar, "@if(\$canSeeModule('orders')");
$end = strpos($sidebar, '@endif', $start);
if ($start === false || $end === false) throw new RuntimeException('Orders sidebar block was not found.');
$block = substr($sidebar, $start, $end + strlen('@endif') - $start);
foreach (['admin', 'superadmin', 'manager', 'hr', 'employee'] as $role) {
    Auth::guard('web')->setUser(new User(['role' => $role]));
    foreach ([false, true] as $enabled) {
        $html = Blade::render($block, ['canSeeModule' => fn ($slug) => $slug === 'orders' && $enabled]);
        $visible = str_contains($html, 'data-sidebar-key="orders"');
        if ($visible !== $enabled) throw new RuntimeException("Orders visibility did not match module access for {$role}.");
    }
}
token_get_all(app('blade.compiler')->compileString($sidebar), TOKEN_PARSE);
echo "PASS: disabled Orders hidden, enabled Orders visible across roles, and sidebar template compilation.\n";
