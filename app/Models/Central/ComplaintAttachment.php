<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintAttachment extends Model
{
    protected $connection = 'central';
    protected $table = 'complaint_attachments';

    protected $fillable = [
        'complaint_id',
        'conversation_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by_type',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(CompanyComplaint::class, 'complaint_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ComplaintConversation::class, 'conversation_id');
    }
}
