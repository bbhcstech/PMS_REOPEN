<?php

namespace App\Models;

class Language extends TenantModel
{
    public $timestamps = false;
    protected $fillable = ['code', 'iso_639_3', 'name', 'scope', 'type', 'flag_url'];

    public static function dropdownOptions()
    {
        $model = new static;
        $schema = $model->getConnection()->getSchemaBuilder();
        // Bootstrap older tenant databases as well as newly provisioned companies.
        if (! $schema->hasTable('languages')) {
            (require database_path('migrations/tenant/2026_10_09_110000_create_and_populate_languages_table.php'))->up();
        } elseif (! $model->getConnection()->table('languages')->exists()) {
            \App\Support\LanguageCatalog::populate($model->getConnection());
        }
        return static::orderBy('name')->get();
    }
}
