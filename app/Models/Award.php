<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Award extends TenantModel
{
    protected $fillable = [
        'user_id',
        'appreciation_id',
        'title',
        'description',
        'award_date',
        'image',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appreciation()
    {
        return $this->belongsTo(Appreciations::class, 'appreciation_id');
    }
}
