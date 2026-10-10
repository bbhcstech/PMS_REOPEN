<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Services\{TenantScope, WorkforceAccess};
use Illuminate\Database\Eloquent\Builder;

trait HasWorkforceRecords
{
    protected static function bootHasWorkforceRecords(): void
    {
        static::addGlobalScope('workforce_company', function (Builder $query) {
            if (! auth()->check() || TenantScope::isPlatformAdmin()) return;
            $companyId = TenantScope::companyId();
            $table = $query->getModel()->getTable();
            $query->whereHas('user', fn ($user) => $user->where('company_id', $companyId ?: 0));
            if (! WorkforceAccess::isAdmin(auth()->user())) $query->where($table . '.user_id', auth()->id());
            if ($query->getModel()->getConnection()->getSchemaBuilder()->hasColumn($table, 'company_id')) {
                $query->where(fn ($q) => $q->where($table . '.company_id', $companyId ?: 0)->orWhereNull($table . '.company_id'));
            }
        });
        static::creating(function ($record) {
            if (! auth()->check() && ! $record->getConnection()->getSchemaBuilder()->hasTable('users')) return;
            $user = User::findOrFail($record->user_id);
            if (auth()->check()) TenantScope::authorizeCompany($user->company_id);
            if (auth()->check() && ! TenantScope::isPlatformAdmin() && ! WorkforceAccess::isAdmin(auth()->user())) {
                abort_unless((int) $user->id === (int) auth()->id(), 403);
            }
            $schema = $record->getConnection()->getSchemaBuilder();
            if ($schema->hasColumn($record->getTable(), 'company_id')) $record->company_id = $user->company_id;
            if (! $schema->hasColumn($record->getTable(), 'staff_category')) return;
            foreach (WorkforceAccess::snapshot($user) as $column => $value) {
                if ($schema->hasColumn($record->getTable(), $column)) $record->setAttribute($column, $value);
            }
        });
        static::updating(function ($record) {
            if (! $record->isDirty('user_id')) return;
            $user = User::findOrFail($record->user_id);
            if (auth()->check()) {
                abort_unless(WorkforceAccess::isAdmin(auth()->user()), 403);
                TenantScope::authorizeCompany($user->company_id);
            }
            $schema = $record->getConnection()->getSchemaBuilder();
            if ($schema->hasColumn($record->getTable(), 'company_id')) $record->company_id = $user->company_id;
            if ($schema->hasColumn($record->getTable(), 'staff_category')) {
                foreach (WorkforceAccess::snapshot($user) as $column => $value) $record->setAttribute($column, $value);
            }
        });
    }
}
