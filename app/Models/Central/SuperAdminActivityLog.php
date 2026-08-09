<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Central SuperAdminActivityLog model.
 * Audit trail for every super-admin action.
 * Lives in pms_central.super_admin_activity_logs.
 */
class SuperAdminActivityLog extends Model
{
    protected $connection = 'central';
    protected $table = 'super_admin_activity_logs';

    protected $fillable = [
        'super_admin_id', 'company_id', 'action',
        'description', 'meta', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Convenience factory — log a super-admin action in one line.
     */
    public static function record(
        string $action,
        ?int $companyId = null,
        ?string $description = null,
        ?array $meta = null,
        ?int $superAdminId = null
    ): self {
        return static::create([
            'super_admin_id' => $superAdminId,
            'company_id'     => $companyId,
            'action'         => $action,
            'description'    => $description,
            'meta'           => $meta,
            'ip_address'     => request()?->ip(),
            'user_agent'     => request()?->userAgent(),
        ]);
    }
}
