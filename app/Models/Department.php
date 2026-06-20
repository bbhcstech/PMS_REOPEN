<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'dpt_name',
        'company_id',
        'dpt_code',
        'parent_dpt_id',
        'added_by',
        'last_updated_by',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    // Parent department (if any)
    public function parent()
{
    return $this->belongsTo(ParentDepartment::class, 'parent_dpt_id');
}


    // All sub departments under this department
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_dpt_id');
    }

    public function employeeDetails()
    {
        return $this->hasMany(EmployeeDetail::class, 'department_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'department_project', 'department_id', 'project_id')
            ->withTimestamps();
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
