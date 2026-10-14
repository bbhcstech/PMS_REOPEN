<?php



namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'date',
        'occassion',
        'type', // Optional: could be 'holiday' or 'weekend'
        'recurring_day',
        'department_id_json',
        'designation_id_json',
        'employment_type_json',
        'notification_sent',
        'archived_at'
        
    ];

    protected $dates = ['date'];

    protected $casts = [
        'date' => 'date',
        'archived_at' => 'datetime',
    ];

    protected static array $tableColumnsCache = [];

    public static function getTableColumns(?string $connection = null): array
    {
        $instance = new static;
        $conn = $connection ?: $instance->getConnectionName() ?: config('database.default');
        $database = $instance->getConnectionResolver()->connection($conn)->getDatabaseName();
        $cacheKey = $conn . ':' . $database . ':' . $instance->getTable();

        if (! isset(static::$tableColumnsCache[$cacheKey])) {
            try {
                static::$tableColumnsCache[$cacheKey] = \Illuminate\Support\Facades\Schema::connection($conn)->getColumnListing($instance->getTable());
            } catch (\Throwable) {
                return [];
            }
        }
        return static::$tableColumnsCache[$cacheKey];
    }

    public static function clearTableColumnsCache(): void
    {
        static::$tableColumnsCache = [];
    }

    protected static function booted()
    {
        static::saving(function (Holiday $holiday) {
            $cols = static::getTableColumns($holiday->getConnectionName());
            if (! empty($cols)) {
                // Ensure 'title' is always set if 'occassion' was provided
                if (empty($holiday->attributes['title']) && ! empty($holiday->attributes['occassion'])) {
                    $holiday->attributes['title'] = $holiday->attributes['occassion'];
                }

                // If 'occassion' column does not exist physically in DB, ensure title holds the value
                if (! in_array('occassion', $cols, true) && ! empty($holiday->attributes['occassion'])) {
                    if (empty($holiday->attributes['title'])) {
                        $holiday->attributes['title'] = $holiday->attributes['occassion'];
                    }
                }

                // Strip attributes that do not exist as physical columns in the database table
                foreach (array_keys($holiday->attributes) as $key) {
                    if (! in_array($key, $cols, true)) {
                        unset($holiday->attributes[$key]);
                    }
                }
            }
        });
    }

    public function getOccassionAttribute($value)
    {
        return $value ?: ($this->attributes['title'] ?? null);
    }

    public function setOccassionAttribute($value): void
    {
        $columns = static::getTableColumns($this->getConnectionName());
        if (in_array('occassion', $columns, true)) {
            $this->attributes['occassion'] = $value;
        } else {
            // Older tenant schemas store the holiday name only in title.
            $this->attributes['title'] = $value;
        }
    }

    public function getGroupIdAttribute($value)
    {
        return $value ?: (string) ($this->attributes['id'] ?? '');
    }

    public function group()
    {
        $cols = static::getTableColumns($this->getConnectionName());
        $key = in_array('group_id', $cols, true) ? 'group_id' : 'id';
        return $this->belongsTo(Holiday::class, $key, $key)->with('holidays');
    }

    public function holidays()
    {
        $cols = static::getTableColumns($this->getConnectionName());
        $key = in_array('group_id', $cols, true) ? 'group_id' : 'id';
        return $this->hasMany(Holiday::class, $key, $key);
    }

}
