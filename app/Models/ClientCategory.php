<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ClientCategory extends TenantModel
{
    protected $fillable = ['name'];
    public function subCategories()
    {
        return $this->hasMany(ClientSubCategory::class);
    }
}
