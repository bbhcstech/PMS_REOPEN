<?php

namespace App\Models\Central;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Central SuperAdmin model.
 *
 * This is the platform-level admin — NOT a company user.
 * It authenticates via the 'superadmin' guard (configured in Phase 6).
 * Lives in pms_central.super_admins.
 */
class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $connection = 'central';
    protected $table = 'super_admins';

    protected $fillable = [
        'name', 'email', 'password', 'profile_image',
        'is_active', 'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function activityLogs(): HasMany
    {
        return $this->hasMany(SuperAdminActivityLog::class);
    }
}
