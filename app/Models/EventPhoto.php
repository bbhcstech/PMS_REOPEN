<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPhoto extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'event_photos';

    protected $fillable = [
        'company_id',
        'event_id',
        'uploaded_by',
        'image_path',
        'thumbnail_path',
        'caption',
        'display_order',
        'is_gallery_cover',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_gallery_cover' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'thumbnail_url',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     | ========================================================================= */

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* =========================================================================
     | ACCESSORS
     | ========================================================================= */

    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return asset('assets/img/illustrations/page-misc-error-light.png');
        }
        return asset($this->image_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            return asset($this->thumbnail_path);
        }
        return $this->image_url;
    }

    /* =========================================================================
     | SCOPES
     | ========================================================================= */

    public function scopeForTenant($query, ?int $companyId = null)
    {
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_gallery_cover', 'desc')
                    ->orderBy('display_order', 'asc')
                    ->orderBy('id', 'desc');
    }
}
