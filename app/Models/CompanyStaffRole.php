<?php

namespace App\Models;

class CompanyStaffRole extends TenantModel
{
    protected $fillable = ['company_id', 'name', 'access_role'];
}
