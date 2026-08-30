<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyComplaint extends Model
{
    use SoftDeletes;

    protected $connection = 'central';
    protected $table = 'company_complaints';

    protected $fillable = [
        'ticket_id',
        'company_id',
        'raised_by_id',
        'raised_by_type',
        'raised_by_name',
        'raised_by_email',
        'subject',
        'category',
        'priority',
        'status',
        'related_module',
        'related_record_id',
        'description',
        'assigned_super_admin_id',
        'assigned_to_name',
        'assigned_at',
        'last_reply_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'last_reply_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ComplaintConversation::class, 'complaint_id')->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ComplaintActivity::class, 'complaint_id')->orderBy('created_at', 'desc');
    }

    public function assignedSuperAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'assigned_super_admin_id');
    }

    public static function generateTicketId(): string
    {
        $year = date('Y');
        $prefix = "CMP-{$year}-";

        $latest = static::on('central')
            ->where('ticket_id', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $number = (int) substr($latest->ticket_id, strlen($prefix)) + 1;
        } else {
            $number = 1001;
        }

        return $prefix . str_pad((string)$number, 5, '0', STR_PAD_LEFT);
    }
}
