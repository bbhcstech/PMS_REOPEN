<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ParentDepartment extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'dpt_name',
        'company_id',
        'dpt_code',
        'added_by',
        'last_updated_by',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    // All sub departments under this parent
    public function departments()
    {
        return $this->hasMany(Department::class, 'parent_dpt_id');
    }

    // Active (non-archived) sub departments under this parent
    public function activeDepartments()
    {
        $query = $this->departments();
        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'archived_at')) {
            $query->whereNull('archived_at');
        }

        return $query;
    }

    // All employees assigned to this parent department
    public function employees()
    {
        return $this->hasMany(EmployeeDetail::class, 'parent_dpt_id');
    }

    public function hasActiveSubDepartments(): bool
    {
        return $this->activeDepartments()->exists();
    }

    public function hasActiveEmployees(): bool
    {
        if (Schema::hasColumn('employee_details', 'parent_dpt_id')
            && $this->employees()->tap(fn ($q) => Department::applyActiveEmployeeConstraint($q))->exists()) {
            return true;
        }

        return Schema::hasColumn('employee_details', 'department_id')
            && $this->departments()
                ->whereHas('employeeDetails', fn ($q) => Department::applyActiveEmployeeConstraint($q))
                ->exists();
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
