<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintActivity extends Model
{
    protected $connection = 'central';
    protected $table = 'complaint_activities';

    protected $fillable = [
        'complaint_id',
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'previous_value',
        'new_value',
        'description',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(CompanyComplaint::class, 'complaint_id');
    }

    public static function log(
        int $complaintId,
        string $action,
        string $actorName,
        string $actorType = 'system',
        ?int $actorId = null,
        ?string $prevVal = null,
        ?string $newVal = null,
        ?string $description = null
    ): self {
        return static::create([
            'complaint_id'   => $complaintId,
            'actor_type'     => $actorType,
            'actor_id'       => $actorId,
            'actor_name'     => $actorName,
            'action'         => $action,
            'previous_value' => $prevVal,
            'new_value'      => $newVal,
            'description'    => $description,
        ]);
    }
}
