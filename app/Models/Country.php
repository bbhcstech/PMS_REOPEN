<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Country extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'phone_code',
        'iso_code',
        'min_digits',
        'max_digits',
        'flag_url',
    ];

    /**
     * Alias for phone_code (dial_code)
     */
    public function getDialCodeAttribute(): ?string
    {
        return $this->phone_code;
    }
}