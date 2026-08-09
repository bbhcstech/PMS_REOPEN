<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base model for all tenant-scoped Eloquent models.
 * Connection is dynamically pointed to the current company's database
 * at runtime by the SetTenantConnection middleware.
 */
abstract class TenantModel extends Model
{
    protected $connection = 'tenant';
}