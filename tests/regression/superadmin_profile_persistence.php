<?php

// Run with: php tests/regression/superadmin_profile_persistence.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\SuperAdminController;
use App\Models\Central\SuperAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

foreach (['central', 'tenant'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'tenant', 'session.driver' => 'array']);
(require database_path('migrations/central/2026_08_08_000002_create_central_super_admins_table.php'))->up();
Schema::connection('tenant')->create('users', function ($table) {
    $table->id(); $table->string('name'); $table->string('email'); $table->string('role');
    $table->string('mobile')->nullable(); $table->string('gender')->nullable(); $table->date('dob')->nullable();
    $table->string('marital_status')->nullable(); $table->string('country')->nullable(); $table->string('language')->nullable();
    $table->text('address')->nullable(); $table->text('about')->nullable(); $table->string('government_id_card')->nullable();
    $table->boolean('email_notifications')->default(true); $table->boolean('google_calendar')->default(false); $table->timestamps();
});
function checkProfile($condition, $message) { if (! $condition) throw new RuntimeException($message); }
$requestFor = function (array $data) {
    $request = Request::create('/superadmin/profile', 'POST', $data);
    $request->setLaravelSession(app('session')->driver());
    app()->instance('request', $request);
    return $request;
};
$data = ['name' => 'Platform Admin', 'email' => 'admin@example.test', 'mobile' => '+91 9876543210',
    'gender' => 'Female', 'date_of_birth' => '1990-05-20', 'marital_status' => 'Married',
    'country' => 'India', 'language' => 'English', 'address' => 'Saved address', 'about' => 'Saved biography',
    'email_notifications' => '1', 'google_calendar' => '1'];
$admin = SuperAdmin::create(['name' => 'Original', 'email' => 'admin@example.test', 'password' => 'existing-password', 'profile_image' => 'uploads/profile/existing.png']);
Auth::guard('super_admin')->setUser($admin);
$controller = new SuperAdminController;
checkProfile(! Schema::connection('central')->hasColumn('super_admins', 'mobile'), 'Old schema fixture already contains profile columns.');
$controller->updateProfile($requestFor($data));
$fresh = SuperAdmin::findOrFail($admin->id);
foreach ($data as $field => $value) {
    $saved = $field === 'date_of_birth' ? $fresh->$field->format('Y-m-d') : $fresh->$field;
    checkProfile((string) $saved === (string) $value, "Central profile field $field did not persist.");
}
checkProfile($fresh->profile_image === 'uploads/profile/existing.png' && $fresh->password === 'existing-password', 'Unrelated account data changed.');
DB::connection('central')->table('super_admins')->where('id', $admin->id)->update(['govt_id_card' => 'uploads/documents/existing.pdf']);
$controller->updateProfile($requestFor(['name' => 'Edited name', 'email' => $data['email'], 'email_notifications' => '0', 'google_calendar' => '0']));
$fresh = SuperAdmin::findOrFail($admin->id);
checkProfile($fresh->mobile === $data['mobile'] && $fresh->country === 'India' && $fresh->govt_id_card === 'uploads/documents/existing.pdf', 'Partial save cleared existing profile fields or document.');
checkProfile(! $fresh->email_notifications && ! $fresh->google_calendar, 'Explicit false preferences were not saved.');
$loaded = $controller->profile($requestFor([]))->getData()['user'];
checkProfile($loaded->name === 'Edited name' && $loaded->language === 'English' && $loaded->date_of_birth->format('Y-m-d') === $data['date_of_birth'], 'Profile did not reload saved values.');

// Render the real form with an isolated country list and no page layout.
$source = file_get_contents(resource_path('views/superadmin/profile.blade.php'));
$formStart = strpos($source, '<form id="superadminProfileForm"');
$formEnd = strpos($source, '</form>', $formStart) + strlen('</form>');
$form = substr($source, $formStart, $formEnd - $formStart);
$form = str_replace('\\App\\Models\\Country::getAllWithPhoneCodes()', '$countries', $form);
$renderForm = fn ($user) => Blade::render($form, ['user' => $user, 'countries' => collect([(object) ['phone_code' => '+91', 'iso_code' => 'IN']]), 'errors' => new Illuminate\Support\ViewErrorBag]);
$html = $renderForm($loaded);
foreach (['9876543210', '1990-05-20', 'existing.pdf', 'Saved address', 'Saved biography', 'value="India"', 'value="English"', 'value="Female" selected', 'value="Married" selected'] as $value) {
    checkProfile(str_contains($html, $value), "Saved value $value is missing from the profile form.");
}
Auth::guard('super_admin')->forgetUser();
$legacy = new User;
$legacy->forceFill(['name' => 'Legacy Admin', 'email' => 'legacy@example.test', 'role' => 'superadmin'])->save();
Auth::guard('web')->setUser($legacy);
config(['database.default' => 'central']);
$legacyData = array_merge($data, ['email' => 'legacy@example.test', 'gender' => 'female', 'marital_status' => 'married']);
$controller->updateProfile($requestFor($legacyData));
$legacyFresh = $controller->profile($requestFor([]))->getData()['user'];
checkProfile(\Carbon\Carbon::parse($legacyFresh->dob)->format('Y-m-d') === $data['date_of_birth'] && $legacyFresh->country === 'India', 'Legacy profile failed to persist DOB or country.');
$legacyHtml = $renderForm($legacyFresh);
checkProfile(str_contains($legacyHtml, '1990-05-20') && str_contains($legacyHtml, 'value="Female" selected') && str_contains($legacyHtml, 'value="Married" selected'), 'Legacy saved selections or DOB disappeared.');
checkProfile(SuperAdmin::findOrFail($admin->id)->name === 'Edited name', 'Legacy save changed central account.');
echo "Super admin profile persistence and rendered form checks passed.\n";
