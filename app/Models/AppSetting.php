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

    public static function getCompanyWorkingDays(): array
    {
        $json = static::valueFor('work_working_days', '["Monday","Tuesday","Wednesday","Thursday","Friday"]');
        $days = json_decode($json, true);
        return is_array($days) && count($days) > 0 ? $days : ["Monday","Tuesday","Wednesday","Thursday","Friday"];
    }

    public static function isWorkingDay($date): bool
    {
        $dayName = \Carbon\Carbon::parse($date)->format('l');
        return in_array($dayName, static::getCompanyWorkingDays());
    }
}
