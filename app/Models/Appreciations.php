<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Appreciations extends TenantModel
{
    protected $fillable = ['title', 'summary', 'status', 'icon', 'color_code', 'given_to', 'given_on'];

    public function awards()
    {
        return $this->hasMany(Award::class, 'appreciation_id');
    }
}
