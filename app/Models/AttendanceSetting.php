<?php

namespace App\Models;

use App\Models\TenantModel;


use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends TenantModel
{
  protected $fillable = [
    'office_start_time',
    'late_time',
    'half_day_threshold_minutes',
    'day_off_threshold_minutes',
  ];
}
