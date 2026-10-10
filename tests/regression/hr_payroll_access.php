<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
// Isolate authorization from database migration setup in this regression.
class PayrollSchemaTestDouble { public static int $calls = 0; public static function ensure(): void { self::$calls++; } }
class_alias(PayrollSchemaTestDouble::class, App\Services\PayrollSchema::class);
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$actor = new class extends App\Models\User {
    public bool $allowed = true;
    public array $checked = [];
    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool {
        $this->checked[] = [$moduleSlug, $permission];
        return $this->allowed;
    }
};
Illuminate\Support\Facades\Auth::guard('web')->setUser($actor);
$authorize = new ReflectionMethod(App\Http\Controllers\PayrollController::class, 'authorizePayroll');
$controller = new App\Http\Controllers\PayrollController;
foreach ([['hr', true, true], ['hr', false, false], ['admin', true, true], ['manager', true, false], ['employee', true, false]] as [$role, $allowed, $expected]) {
    $actor->role = $role;
    $actor->allowed = $allowed;
    $passed = true;
    try { $authorize->invoke($controller, 'payroll', 'view'); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $e) { if ($e->getStatusCode() !== 403) throw $e; $passed = false; }
    if ($passed !== $expected) throw new RuntimeException('Unexpected payroll access for ' . $role);
}
$actor->role = 'hr'; $actor->allowed = false;
try { $authorize->invoke($controller, 'payroll', 'approve'); throw new RuntimeException('Approval permission bypassed.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {}
if (end($actor->checked) !== ['payroll', 'approve']) throw new RuntimeException('Action permission not checked.');
if (PayrollSchemaTestDouble::$calls !== 2) throw new RuntimeException('Denied actors reached payroll schema.');
echo "PASS: permitted HR/Admin payroll access, revoked HR access, action permission checks and employee/manager restrictions.\n";
