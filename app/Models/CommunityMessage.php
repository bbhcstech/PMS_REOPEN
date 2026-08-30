<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CommunityMessage extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'community_messages';

    protected $fillable = [
        'company_id',
        'user_id',
        'parent_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
        'is_pinned',
        'pinned_by',
        'pinned_at',
        'edited_at',
        'deleted_by',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'attachment_url',
        'formatted_time',
        'formatted_date',
        'is_edited',
    ];

    /**
     * Tenant Scoping
     */
    public function scopeForTenant($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(CommunityMessage::class, 'parent_id')->withTrashed();
    }

    public function replies()
    {
        return $this->hasMany(CommunityMessage::class, 'parent_id');
    }

    public function reactions()
    {
        return $this->hasMany(CommunityReaction::class, 'message_id');
    }

    public function pinnedBy()
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    /**
     * Accessors
     */
    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment_path) {
            return null;
        }

        if (str_starts_with($this->attachment_path, 'http://') || str_starts_with($this->attachment_path, 'https://')) {
            return $this->attachment_path;
        }

        return asset($this->attachment_path);
    }

    public function getFormattedTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('h:i A') : '';
    }

    public function getFormattedDateAttribute()
    {
        if (!$this->created_at) {
            return '';
        }

        if ($this->created_at->isToday()) {
            return 'Today';
        }

        if ($this->created_at->isYesterday()) {
            return 'Yesterday';
        }

        return $this->created_at->format('d M Y');
    }

    public function getIsEditedAttribute()
    {
        return !is_null($this->edited_at);
    }
}
