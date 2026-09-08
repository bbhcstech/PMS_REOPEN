<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintConversation extends Model
{
    protected $connection = 'central';
    protected $table = 'complaint_conversations';

    protected $fillable = [
        'complaint_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'sender_email',
        'message',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(CompanyComplaint::class, 'complaint_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class, 'conversation_id');
    }
}
