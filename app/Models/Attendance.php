<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends TenantModel
{
    // explicit mapping to your table
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'location_id',
        'latitude',
        'longitude',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_address',
        'clock_in_photo',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_address',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'working_from',
        'late',
        'half_day',
        'half_day_type',
        'work_from_type',
        'overwrite_attendance',
        'archived_at'
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // keep clock_in/clock_out as strings (DB stores TIME)
        'clock_in' => 'string',
        'clock_out' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
        'clock_in_latitude' => 'float',
        'clock_in_longitude' => 'float',
        'clock_out_latitude' => 'float',
        'clock_out_longitude' => 'float',
        'archived_at' => 'datetime',
    ];

    protected $appends = [
        'total_duration',      // H:i:s
        'total_seconds',       // integer seconds
        'clock_in_datetime',
        'clock_out_datetime'
    ];

    public function getLocationAttribute($value = null)
    {
        return $value ?: ($this->attributes['location'] ?? $this->attributes['working_from'] ?? $this->attributes['clock_in_address'] ?? null);
    }

    public function getLatitudeAttribute($value)
    {
        return $value !== null ? (float) $value : (isset($this->attributes['clock_in_latitude']) ? (float) $this->attributes['clock_in_latitude'] : null);
    }

    public function getLongitudeAttribute($value)
    {
        return $value !== null ? (float) $value : (isset($this->attributes['clock_in_longitude']) ? (float) $this->attributes['clock_in_longitude'] : null);
    }

    // fully-qualified relation to avoid namespace issues
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Parse an input value (time-only, full-datetime, Carbon/DateTime) into Carbon or null.
     */
    protected function parseDatetimeValue($value, string $attendanceDate): ?Carbon
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        $defaultTz = (config('app.timezone') && config('app.timezone') !== 'UTC') ? config('app.timezone') : 'Asia/Kolkata';

        $val = trim((string) $value);
        if ($attendanceDate instanceof \DateTimeInterface) {
            $dateOnly = $attendanceDate->format('Y-m-d');
        } else {
            $dateOnly = substr(trim((string) $attendanceDate), 0, 10);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $val)) {
            try { return Carbon::parse($val, $defaultTz); } catch (\Throwable $e) {}
        }

        if (preg_match('/^[0-2]?\d:[0-5]\d(:[0-5]\d)?(\s?[AP]M)?$/i', $val)) {
            $tmp = $val;
            if (preg_match('/^\d{1,2}:\d{2}$/', $tmp)) {
                $tmp .= ':00';
            }
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', $dateOnly . ' ' . $tmp, $defaultTz);
            } catch (\Throwable $e) {
                try { return Carbon::parse($dateOnly . ' ' . $tmp, $defaultTz); } catch (\Throwable $_) {}
            }
        }

        try { return Carbon::parse($dateOnly . ' ' . $val, $defaultTz); } catch (\Throwable $e) {}
        try { return Carbon::parse($val, $defaultTz); } catch (\Throwable $e) {}

        return null;
    }

    // combine date + clock_in into a Carbon (or null)
    public function getClockInDatetimeAttribute()
    {
        if (empty($this->clock_in) || empty($this->date)) {
            return null;
        }

        return $this->parseDatetimeValue($this->clock_in, $this->date);
    }

    // combine date + clock_out into a Carbon (or null)
    public function getClockOutDatetimeAttribute()
    {
        if (empty($this->clock_out) || empty($this->date)) {
            return null;
        }

        return $this->parseDatetimeValue($this->clock_out, $this->date);
    }

    // seconds between in/out. If out < in => treat out as next day.
    // If clocked in without clock_out (open session), calculates elapsed active work time.
    public function getTotalSecondsAttribute()
    {
        $in = $this->clock_in_datetime;
        $out = $this->clock_out_datetime;

        if (! $in) {
            return 0;
        }

        if ($out) {
            if ($out->lt($in)) {
                $out = $out->copy()->addDay();
            }

            $seconds = $out->getTimestamp() - $in->getTimestamp();
            return max(0, (int) $seconds);
        }

        // Active/open shift without clock_out yet:
        $defaultTz = (config('app.timezone') && config('app.timezone') !== 'UTC') ? config('app.timezone') : 'Asia/Kolkata';
        $now = Carbon::now($defaultTz);
        $diff = $now->getTimestamp() - $in->getTimestamp();

        if ($diff > 0) {
            // For active sessions within 24 hours, return live elapsed work seconds
            if ($diff <= 86400) {
                return (int) $diff;
            }
            // For unclosed shifts older than 24 hours, cap at standard day shift (8.5 hours = 30600 seconds)
            return 30600;
        }

        return 0;
    }

    // human-readable H:i:s (hours may be >24)
    public function getTotalDurationAttribute()
    {
        $seconds = (int) $this->total_seconds;

        if ($seconds <= 0) {
            return '00:00:00';
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    protected static array $columnsCache = [];

    /**
     * Determine whether the underlying database table has the specified column.
     */
    public function hasTableColumn(string $column): bool
    {
        $connectionName = $this->getConnectionName() ?: config('database.default');
        $key = $connectionName . ':' . $this->getTable();

        if (! isset(static::$columnsCache[$key])) {
            try {
                static::$columnsCache[$key] = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
            } catch (\Throwable $e) {
                return false;
            }
        }

        return in_array($column, static::$columnsCache[$key], true);
    }

    /*
     * Mutators ensuring only valid columns are written to the database
     */
    public function setLocationAttribute($value)
    {
        if ($this->hasTableColumn('location')) {
            $this->attributes['location'] = $value;
        } else {
            $this->attributes['clock_in_address'] = $value;
        }
    }

    public function setLatitudeAttribute($value)
    {
        if ($this->hasTableColumn('latitude')) {
            $this->attributes['latitude'] = $value;
        } else {
            $this->attributes['clock_in_latitude'] = $value;
        }
    }

    public function setLongitudeAttribute($value)
    {
        if ($this->hasTableColumn('longitude')) {
            $this->attributes['longitude'] = $value;
        } else {
            $this->attributes['clock_in_longitude'] = $value;
        }
    }

    public function setWorkingFromAttribute($value)
    {
        if ($this->hasTableColumn('working_from')) {
            $this->attributes['working_from'] = $value;
        } elseif ($this->hasTableColumn('work_from_type')) {
            $this->attributes['work_from_type'] = $value;
        }
    }

    public function setLateAttribute($value)
    {
        if ($this->hasTableColumn('late')) {
            $this->attributes['late'] = $value;
        } elseif ($value === 'yes' && ($this->attributes['status'] ?? null) === 'present') {
            $this->attributes['status'] = 'late';
        }
    }

    public function setHalfDayAttribute($value)
    {
        if ($this->hasTableColumn('half_day')) {
            $this->attributes['half_day'] = $value;
        } elseif ($value === 'yes') {
            $this->attributes['status'] = 'half_day';
        }
    }

    public function setDepartmentIdAttribute($value)
    {
        if ($this->hasTableColumn('department_id')) {
            $this->attributes['department_id'] = $value;
        }
    }

    public function setLocationIdAttribute($value)
    {
        if ($this->hasTableColumn('location_id')) {
            $this->attributes['location_id'] = $value;
        }
    }

    public function setHalfDayTypeAttribute($value)
    {
        if ($this->hasTableColumn('half_day_type')) {
            $this->attributes['half_day_type'] = $value;
        }
    }

    public function setOverwriteAttendanceAttribute($value)
    {
        if ($this->hasTableColumn('overwrite_attendance')) {
            $this->attributes['overwrite_attendance'] = $value;
        }
    }

    /*
     * Accessors ensuring backward-compatible property access
     */
    public function getLocationAttribute()
    {
        return $this->attributes['location'] ?? ($this->attributes['clock_in_address'] ?? null);
    }

    public function getLatitudeAttribute()
    {
        return $this->attributes['latitude'] ?? ($this->attributes['clock_in_latitude'] ?? null);
    }

    public function getLongitudeAttribute()
    {
        return $this->attributes['longitude'] ?? ($this->attributes['clock_in_longitude'] ?? null);
    }

    public function getLateAttribute()
    {
        return $this->attributes['late'] ?? (($this->status ?? '') === 'late' ? 'yes' : 'no');
    }

    public function getHalfDayAttribute()
    {
        return $this->attributes['half_day'] ?? (($this->status ?? '') === 'half_day' ? 'yes' : 'no');
    }

    public function getWorkingFromAttribute()
    {
        return $this->attributes['working_from'] ?? ($this->attributes['work_from_type'] ?? null);
    }
}
