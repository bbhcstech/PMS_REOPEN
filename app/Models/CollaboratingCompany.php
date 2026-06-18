<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollaboratingCompany extends Model
{
    protected $fillable = [
        'name',
        'industry',
        'collaboration_type',
        'description',
        'services',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'social_links',
        'status',
        'started_on',
        'notes',
    ];

    protected $casts = [
        'social_links' => 'array',
        'started_on' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
