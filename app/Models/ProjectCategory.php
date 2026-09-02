<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ProjectCategory extends TenantModel
{
    protected $table = 'project_category'; // 👈 match your DB table name
    protected $fillable = ['category_name'];
}

