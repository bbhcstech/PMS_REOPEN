<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends TenantModel
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'label',
        'description',
        'value',
        'type',
        'options',
        'min_value',
        'max_value',
        'unit',
        'placeholder',
        'section',
        'page',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public static function valueFor(string $key, ?string $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
