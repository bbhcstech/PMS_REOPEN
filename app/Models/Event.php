<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'event_type',
        'description',
        'banner',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'location_type',
        'location',
        'meeting_url',
        'organizer_id',
        'max_participants',
        'rsvp_required',
        'reminder',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rsvp_required' => 'boolean',
        'max_participants' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug) && ! empty($event->title)) {
                $event->slug = Str::slug($event->title) . '-' . Str::random(5);
            }
        });
    }

    /* =========================================================================
     | RELATIONSHIPS
     | ========================================================================= */

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function rsvps()
    {
        return $this->hasMany(EventRsvp::class, 'event_id');
    }

    public function photos()
    {
        return $this->hasMany(EventPhoto::class, 'event_id')->ordered();
    }

    public function galleryCover()
    {
        return $this->hasOne(EventPhoto::class, 'event_id')->where('is_gallery_cover', true);
    }

    /* =========================================================================
     | SCOPES (TENANT ISOLATION & FILTERING)
     | ========================================================================= */

    public function scopeForTenant($query, ?int $companyId)
    {
        if ($companyId) {
            return $query->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            });
        }
        return $query;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where('start_date', '>=', $today)
                     ->where('status', '!=', 'cancelled')
                     ->orderBy('start_date', 'asc')
                     ->orderBy('start_time', 'asc');
    }

    public function scopeToday($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $today);
                     })
                     ->where('status', '!=', 'cancelled');
    }

    public function scopePast($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where(function ($q) use ($today) {
            $q->whereNotNull('end_date')->where('end_date', '<', $today)
              ->orWhere(function ($q2) use ($today) {
                  $q2->whereNull('end_date')->where('start_date', '<', $today);
              });
        });
    }

    /* =========================================================================
     | ACCESSORS & HELPERS
     | ========================================================================= */

    public function getBannerUrlAttribute(): ?string
    {
        if ($this->banner && file_exists(public_path($this->banner))) {
            return asset($this->banner);
        }
        if ($this->banner && str_starts_with($this->banner, 'http')) {
            return $this->banner;
        }
        return null;
    }

    public function getUserRsvpAttribute()
    {
        $userId = auth()->id();
        if (! $userId) return null;
        return $this->rsvps->firstWhere('user_id', $userId);
    }

    public function getRsvpCountsAttribute(): array
    {
        $rsvps = $this->rsvps;
        return [
            'going' => $rsvps->where('response', 'going')->count(),
            'maybe' => $rsvps->where('response', 'maybe')->count(),
            'not_going' => $rsvps->where('response', 'not_going')->count(),
            'total' => $rsvps->count(),
        ];
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'published' => 'bg-label-success',
            'draft' => 'bg-label-warning',
            'cancelled' => 'bg-label-danger',
            'completed' => 'bg-label-info',
            default => 'bg-label-secondary',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match (strtolower($this->event_type)) {
            'meeting' => 'bg-label-primary',
            'holiday' => 'bg-label-danger',
            'training' => 'bg-label-info',
            'workshop' => 'bg-label-warning',
            'conference' => 'bg-label-dark',
            'team building' => 'bg-label-success',
            'birthday', 'anniversary' => 'bg-label-tertiary',
            default => 'bg-label-secondary',
        };
    }
}
