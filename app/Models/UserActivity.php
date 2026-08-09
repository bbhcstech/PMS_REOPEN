<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends TenantModel
{
    protected $fillable = [
        'company_id',
        'user_id',
        'activity',
    ];
    
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

}
