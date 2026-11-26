<?php

namespace App\Models;

class CompanyStaffRole extends TenantModel
{
    protected $fillable = ['company_id', 'name', 'access_role'];

    public function permissionKey(): string
    {
        return 'staff:'.$this->company_id.':'.$this->id;
    }
}
