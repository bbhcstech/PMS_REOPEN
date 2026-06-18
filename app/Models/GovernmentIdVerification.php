<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentIdVerification extends Model
{
    protected $fillable = [
        'user_id',
        'employee_detail_id',
        'submitted_dob',
        'image_path',
        'ocr_text',
        'ocr_detected_dob',
        'verification_status',
        'ocr_message',
        'ocr_errors',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_dob' => 'date',
        'ocr_errors' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeDetail()
    {
        return $this->belongsTo(EmployeeDetail::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
