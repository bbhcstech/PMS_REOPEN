<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A letter written and sent from Letter Head > Write & Send Letter (one row per letter).
 */
class LetterheadSentLetter extends TenantModel
{
    protected $table = 'letterhead_sent_letters';

    protected $fillable = [
        'company_id', 'letterhead_id', 'template_key', 'ref_no', 'letter_date',
        'recipient_name', 'recipient_email', 'subject', 'body',
        'signatory_name', 'signatory_title',
        'signature_id', 'signature_snapshot_path', 'signature_alignment',
        'signature_width_mm', 'signature_applied_by', 'signature_applied_at',
        'pdf_path', 'delivery_status', 'delivery_error', 'sent_by',
    ];

    protected $casts = [
        'signature_width_mm' => 'integer',
        'signature_applied_at' => 'datetime',
    ];

    public function letterhead(): BelongsTo
    {
        return $this->belongsTo(Letterhead::class, 'letterhead_id')->withTrashed();
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public static function ensureTable(): void
    {
        if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('letterhead_sent_letters')) {
            (require database_path('migrations/tenant/2026_10_10_160000_create_letterhead_sent_letters_table.php'))->up();
        }
        if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('letterhead_sent_letters', 'signature_snapshot_path')) {
            (require database_path('migrations/tenant/2026_11_28_000002_add_signature_snapshot_to_letterhead_sent_letters_table.php'))->up();
        }
    }

    public function getDeliveryLabelAttribute(): string
    {
        return match ($this->delivery_status) {
            'sent' => 'Emailed',
            'failed' => 'Email failed',
            default => 'Saved',
        };
    }
}
