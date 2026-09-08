<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventRsvp extends TenantModel
{
    use HasFactory;

    protected $table = 'event_rsvps';

    protected $fillable = [
        'event_id',
        'user_id',
        'company_id',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
