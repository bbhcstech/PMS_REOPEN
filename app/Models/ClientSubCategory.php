<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ClientSubCategory extends TenantModel
{
    protected $fillable = ['name', 'client_category_id'];
    public function category()
    {
        return $this->belongsTo(ClientCategory::class);
    }
}
