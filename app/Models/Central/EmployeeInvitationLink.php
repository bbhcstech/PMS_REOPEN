<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class EmployeeInvitationLink extends Model
{
    protected $connection = 'central';
    protected $table = 'central_employee_invitation_links';

    protected $fillable = [
        'company_id',
        'token_hash',
        'token_type',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
