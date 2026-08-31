<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityUserState extends TenantModel
{
    use HasFactory;

    protected $table = 'community_user_states';

    protected $fillable = [
        'company_id',
        'user_id',
        'last_read_message_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function scopeForTenant($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
