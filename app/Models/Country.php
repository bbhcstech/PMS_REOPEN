<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Country extends TenantModel
{
     protected $fillable = [
        'name'
    ];
}