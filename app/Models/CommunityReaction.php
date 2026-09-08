<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityReaction extends TenantModel
{
    use HasFactory;

    protected $table = 'community_reactions';

    protected $fillable = [
        'company_id',
        'message_id',
        'user_id',
        'emoji',
    ];

    public function scopeForTenant($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function message()
    {
        return $this->belongsTo(CommunityMessage::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
