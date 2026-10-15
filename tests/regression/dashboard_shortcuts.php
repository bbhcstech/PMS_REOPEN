<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\DashboardShortcuts;
use Illuminate\Support\Facades\{DB, Route, Schema};

app()->instance('db.connector.sqlite', new class implements Illuminate\Database\Connectors\ConnectorInterface {
    private array $databases = [];
    public function connect(array $config) { return $this->databases[$config['database']] ??= new PDO('sqlite::memory:'); }
});
function shortcutsCheck($condition, $message) { if (! $condition) throw new RuntimeException($message); }
function shortcutsTenant($name) {
    config(['database.connections.tenant' => ['driver' => 'sqlite', 'database' => $name, 'prefix' => '']]);
    DB::purge('tenant');
}
foreach (['alpha', 'beta'] as $tenant) {
    shortcutsTenant($tenant);
    Schema::connection('tenant')->create('app_settings', function ($t) {
        $t->id(); foreach (['key', 'label', 'value', 'type', 'page', 'section'] as $column) $t->string($column);
        $t->unique('key'); $t->integer('sort_order'); $t->timestamps();
    });
    Schema::connection('tenant')->create('modules', function ($t) {
        $t->id(); $t->string('slug'); $t->string('name'); $t->string('route_name'); $t->boolean('is_active'); $t->integer('sort_order');
    });
}
$service = new DashboardShortcuts();
$user = new class extends User {
    public function normalizedRole(): string { return $this->role ?? 'admin'; }
    public function canViewModule(string $slug): bool { return $slug !== 'blocked'; }
};
Route::setRoutes(new Illuminate\Routing\RouteCollection());
Route::get('/shortcut-test/visible', fn () => 'ok')->name('shortcut-test.visible');
Route::get('/shortcut-test/record/{id}', fn () => 'ok')->name('shortcut-test.record');
Route::post('/shortcut-test/post', fn () => 'ok')->name('shortcut-test.post');
Route::get('/shortcut-test/custom', fn () => 'ok')->name('shortcut-test.custom');
Route::getRoutes()->refreshNameLookups();
config(['workspace_modules' => [
    'visible' => ['name' => 'Visible', 'route' => 'shortcut-test.visible'],
    'blocked' => ['name' => 'Blocked', 'route' => 'shortcut-test.visible'],
    'record' => ['name' => 'Record', 'route' => 'shortcut-test.record'],
    'missing' => ['name' => 'Missing', 'route' => 'shortcut-test.missing'],
    'post' => ['name' => 'Post', 'route' => 'shortcut-test.post'],
]]);
shortcutsTenant('alpha');
shortcutsCheck(! $service->enabled() && $service->groups($user)->isEmpty(), 'New company must default to hidden.');
$service->save(true);
shortcutsCheck($service->enabled(), 'Active state was not saved.');
$labels = $service->groups($user)->flatten(1)->pluck('label')->all();
shortcutsCheck($labels === ['Visible'], 'Unavailable or unsafe shortcut leaked: '.json_encode($labels));
DB::connection('tenant')->table('modules')->insert(['slug' => 'custom', 'name' => 'New company module', 'route_name' => 'shortcut-test.custom', 'is_active' => true, 'sort_order' => 1]);
shortcutsCheck($service->groups($user)->flatten(1)->pluck('label')->all() === ['Visible', 'New company module'], 'New company module missing.');
$user->role = 'employee';
shortcutsCheck($service->groups($user)->isEmpty(), 'Employee received admin shortcuts.');
$user->role = 'admin';
shortcutsTenant('beta');
shortcutsCheck(! $service->enabled(), 'Company preference leaked across tenants.');
shortcutsTenant('alpha');
shortcutsCheck($service->enabled(), 'Company preference did not survive reload.');
$service->save(false);
shortcutsCheck(! $service->enabled() && $service->groups($user)->isEmpty(), 'Inactive state did not hide shortcuts.');
shortcutsCheck(DB::connection('tenant')->table('app_settings')->count() === 1, 'Saving duplicated preference.');
$request = Illuminate\Http\Request::create('/admin/settings/app/dashboard', 'POST', ['dashboard_shortcuts_enabled' => '1']);
$request->setUserResolver(fn () => $user);
$user->role = 'employee';
try {
    (new App\Http\Controllers\Admin\Settings\AppSettingController())->updateDashboard($request, $service);
    throw new RuntimeException('Non-admin save was accepted.');
} catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
    shortcutsCheck($e->getStatusCode() === 403, 'Wrong authorization response.');
}
$user->role = 'admin';
config(['session.driver' => 'array']);
(new App\Http\Controllers\Admin\Settings\AppSettingController())->updateDashboard($request, $service);
shortcutsCheck($service->enabled(), 'Admin controller did not persist active state.');
$request->merge(['dashboard_shortcuts_enabled' => '0']);
(new App\Http\Controllers\Admin\Settings\AppSettingController())->updateDashboard($request, $service);
shortcutsCheck(! $service->enabled(), 'Admin controller did not persist inactive state.');
$request->merge(['dashboard_shortcuts_enabled' => 'invalid']);
try {
    (new App\Http\Controllers\Admin\Settings\AppSettingController())->updateDashboard($request, $service);
    throw new RuntimeException('Invalid preference accepted.');
} catch (Illuminate\Validation\ValidationException $e) {
    shortcutsCheck(! $service->enabled(), 'Invalid input changed preference.');
}
echo "Dashboard shortcuts: defaults, persistence, tenant isolation, filtering and authorization passed.\n";
