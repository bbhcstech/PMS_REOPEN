<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Client extends TenantModel
{
    protected $fillable = [
        'salutation',
        'name',
        'email',
        'company_name',
        'password',
        'country',
        'mobile',
        'profile_picture',
        'gender',
        'language',
        'client_category_id',
        'client_sub_category_id',
        'login_allowed',
        'email_notifications',
        'website',
        'tax_name',
        'tax_number',
        'office_phone',
        'city',
        'state',
        'postal_code',
        'added_by',
        'company_address',
        'shipping_address',
        'note',
        'company_logo',
        'status',
        'client_uid',
    ];

    protected $attributes = [
        'status' => 'active',
        'login_allowed' => 1,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            $latest = static::orderBy('id', 'desc')->first();
            $number = $latest ? $latest->id + 1 : 1;

            $client->client_uid = 'XINK-CL-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ClientSubCategory::class, 'client_sub_category_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function countryRel()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function getProfileImageAttribute()
    {
        return $this->profile_picture;
    }
}

