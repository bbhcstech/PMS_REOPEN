<?php
// Run with: php tests/regression/client_languages.php
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

foreach (['tenant', 'central'] as $connection) {
    config(["database.connections.$connection" => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge($connection);
}
config(['database.default' => 'central', 'session.driver' => 'array']);
function checkLanguages($ok, $message) { if (! $ok) throw new RuntimeException($message); }
$languages = Language::dropdownOptions();
checkLanguages($languages->count() === 7927, 'The full ISO 639-3 catalog was not stored.');
checkLanguages(! Schema::connection('central')->hasTable('languages'), 'Language storage used the wrong database.');
foreach (['en' => 'English', 'bn' => 'Bengali', 'hi' => 'Hindi', 'ja' => 'Japanese', 'ta' => 'Tamil', 'ase' => 'American Sign Language', 'aaa' => 'Ghotuo'] as $code => $name) {
    checkLanguages($languages->firstWhere('code', $code)?->name === $name, "Language $code is missing or has wrong code.");
}
$existingId = $languages->firstWhere('code', 'en')->id;
DB::connection('tenant')->table('languages')->where('code', 'en')->update(['name' => 'English (database label)']);
$migration = require database_path('migrations/tenant/2026_10_09_110000_create_and_populate_languages_table.php');
$migration->up(); $migration->up();
checkLanguages(Language::count() === 7927 && Language::where('code', 'en')->value('id') === $existingId, 'Repeated migration duplicated entries or changed IDs.');
checkLanguages(Language::where('code', 'en')->value('name') === 'English (database label)', 'Migration overwrote existing database labels.');
$html = view('admin.clients.partials.language-options', ['languages' => Language::dropdownOptions(), 'selectedLanguage' => 'ase'])->render();
checkLanguages(str_contains($html, 'English (database label)') && str_contains($html, 'American Sign Language'), 'Dropdown is not reading the database.');
checkLanguages(preg_match('/value="ase"[^>]*selected/', $html) === 1 && substr_count($html, 'selected') === 1, 'Saved selection is missing or multiple options were selected.');
$emptySelection = view('admin.clients.partials.language-options', ['languages' => $languages, 'selectedLanguage' => ''])->render();
checkLanguages(! str_contains($emptySelection, 'selected'), 'Cleared language preference was replaced.');
DB::connection('tenant')->table('languages')->where('code', 'aaa')->delete();
$migration->up();
checkLanguages(Language::where('code', 'aaa')->exists(), 'Rerun failed to restore a missing catalog entry.');
DB::connection('tenant')->table('languages')->delete();
checkLanguages(Language::dropdownOptions()->count() === 7927, 'Empty language catalog was not populated.');
foreach (['create', 'edit', 'partials/language-options'] as $view) {
    token_get_all(app('blade.compiler')->compileString(file_get_contents(resource_path('views/admin/clients/' . $view . '.blade.php'))), TOKEN_PARSE);
}
echo "Client language database, catalog, migration and dropdown checks passed.\n";
