<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CentralNotification extends Model
{
    protected $connection = 'central';
    protected $table = 'central_notifications';

    protected $fillable = [
        'notification_id',
        'company_id',
        'type',
        'title',
        'message',
        'severity',
        'related_module',
        'related_record_id',
        'action_url',
        'target_audience',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public static function createNotification(array $data): self
    {
        if (empty($data['notification_id'])) {
            $data['notification_id'] = 'NTF-' . date('Ymd') . '-' . Str::random(6);
        }

        return static::create($data);
    }
}
