<?php

namespace App\Models;

use App\Models\TenantModel;

class AttendanceSetting extends TenantModel
{
  protected $fillable = [
    'office_start_time',
    'late_time',
    'half_day_threshold_minutes',
    'day_off_threshold_minutes',
    // Clock-in / clock-out boundary (see App\Services\AttendanceClockPolicy)
    'clock_window_enabled',
    'clock_window_enabled_at',
    'clock_in_opens_at',
    'clock_in_closes_at',
    'half_day_after_time',
    'office_end_time',
    'clock_out_closes_at',
    'missed_clock_out_status',
  ];

  protected $casts = [
    'clock_window_enabled' => 'boolean',
  ];
}
