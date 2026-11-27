<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class LetterheadSignature extends TenantModel
{
    use SoftDeletes;

    protected $table = 'letterhead_signatures';

    protected $fillable = [
        'company_id',
        'user_id',
        'signatory_name',
        'signatory_title',
        'image_path',
        'mime_type',
        'width_px',
        'height_px',
        'method',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'width_px' => 'integer',
        'height_px' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, User $actor, ?int $companyId = null): Builder
    {
        $role = method_exists($actor, 'normalizedRole') ? $actor->normalizedRole() : strtolower((string) $actor->role);
        $isOrgAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'hr'], true);
        $resolvedCompanyId = $companyId ?? ($actor->company_id ? (int) $actor->company_id : null);

        if ($resolvedCompanyId) {
            $query->where('company_id', $resolvedCompanyId);
        } elseif ($role !== 'superadmin') {
            $query->where('user_id', $actor->id);
        }

        if (! $isOrgAdmin) {
            $query->where('user_id', $actor->id);
        }

        return $query;
    }

    public static function ensureTable(): void
    {
        if (! Schema::connection('tenant')->hasTable('letterhead_signatures')) {
            (require database_path('migrations/tenant/2026_11_28_000001_create_letterhead_signatures_table.php'))->up();
        }
    }
}
