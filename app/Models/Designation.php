<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'order',             // DB column name
        'company_id',
        'name',
        'parent_id',
        'unique_code',
        'added_by',
        'last_updated_by',
        'status',
        'level',              // Added this line
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Legacy rows without company_id are visible only to their creator's company.
        static::addGlobalScope('designation_company', function ($query) {
            if (! auth()->check()) {
                $query->whereRaw('1 = 0');
                return;
            }
            $companyId = \App\Services\DesignationLevels::companyId();
            $query->where(function ($owned) use ($companyId) {
                $owned->where('designations.company_id', $companyId)
                    ->orWhere(function ($legacy) use ($companyId) {
                        $legacy->whereNull('designations.company_id')->whereHas('addedBy', fn ($creator) => $creator->where('company_id', $companyId));
                    });
            });
        });

        static::saving(function ($model) {
            $companyId = \App\Services\DesignationLevels::companyId();
            abort_if($model->company_id && (int) $model->company_id !== $companyId, 403);
            $model->company_id = $companyId;
            if (! $model->exists || $model->isDirty(['level', 'parent_id'])) {
                $maximum = \App\Services\DesignationLevels::maximum();
                validator(['level' => $model->level ?? 0], [
                    'level' => ['required', 'integer', 'min:0', 'max:' . $maximum],
                ])->validate();
                if ($model->parent_id) {
                    $parent = static::find($model->parent_id);
                    if (! $parent || (int) $model->parent_id === (int) $model->id || (int) $model->level <= (int) $parent->level) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'parent_id' => 'Select a parent in your company with a lower designation level.',
                        ]);
                    }
                }
                if ($model->exists && static::where('parent_id', $model->id)->where('level', '<=', (int) $model->level)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'level' => 'The designation level must be lower than its subordinate levels.',
                    ]);
                }
            }
        });

        // Automatically generate unique_code after creation if column exists
        static::created(function ($model) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('designations', 'unique_code') && empty($model->unique_code)) {
                $model->unique_code = 'DGN-' . str_pad($model->id, 4, '0', STR_PAD_LEFT);
                $model->saveQuietly();
            }
            $model->getConnection()->afterCommit(function () use ($model) {
                \App\Services\DesignationLevels::expandAfterAdminCreation((int) $model->level);
            });
        });
    }

    // RELATIONSHIPS
    public function scopeWithinLevelLimit($query)
    {
        return $query->where(function ($levels) {
            $levels->whereNull('level')
                ->orWhereBetween('level', [0, \App\Services\DesignationLevels::maximum()]);
        });
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class, 'designation_id');
    }

    public function parent()
    {
        return $this->belongsTo(Designation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Designation::class, 'parent_id')->whereNull('archived_at');
    }
}
