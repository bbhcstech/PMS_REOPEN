<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'type',
        'duration',
        'date',
        'start_date',
        'end_date',
        'total_days',
        'paid_days',
        'unpaid_days',
        'reason',
        'files',
        'attachment',
        'apology_note',
        'emergency_flag',
        'half_day_flag',
        'contact_during_leave',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'is_paid',
        'is_unpaid',
        'paid',
        'payroll_deduction_flag',
        'admin_note',
        'leave_year',
        'archived_at',
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:2',
        'paid_days' => 'decimal:2',
        'unpaid_days' => 'decimal:2',
        'emergency_flag' => 'boolean',
        'half_day_flag' => 'boolean',
        'is_paid' => 'boolean',
        'is_unpaid' => 'boolean',
        'paid' => 'boolean',
        'payroll_deduction_flag' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class);
    }

    public function apologyLetters()
    {
        return $this->hasMany(LeaveApologyLetter::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function getAttachmentPathAttribute(): ?string
    {
        return $this->attachment ?: $this->files;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->leaveType?->name ?: ucwords(str_replace(['-', '_'], ' ', (string) $this->type));
    }
}
