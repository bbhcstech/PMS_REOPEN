<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealStage extends TenantModel
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'order', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function getDefaultProbabilityAttribute(): int
    {
        $name = strtolower($this->name ?? '');

        if (str_contains($name, 'won')) return 100;
        if (str_contains($name, 'lost')) return 0;
        if (str_contains($name, 'verbal') || str_contains($name, 'agreement')) return 90;
        if (str_contains($name, 'negotiat')) return 75;
        if (str_contains($name, 'proposal') || str_contains($name, 'appointment')) return 50;
        if (str_contains($name, 'qualified') || str_contains($name, 'contact')) return 25;
        return 10;
    }
}
