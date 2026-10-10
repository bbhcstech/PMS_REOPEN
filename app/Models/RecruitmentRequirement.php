<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentRequirement extends TenantModel
{
    protected $table = 'recruitment_requirements';

    protected $fillable = [
        'company_id',
        'title',
        'department_id',
        'department_name',
        'positions',
        'employment_type',
        'experience_required',
        'salary_range',
        'location',
        'description',
        'requirements_summary',
        'status',
        'created_by',
        'pdf_header_image',
        'pdf_footer_image',
    ];

    protected $casts = [
        'positions' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Header/footer image as an embeddable data URI for the PDF (DomPDF needs no file or network access). */
    public function pdfImageDataUri(string $column): ?string
    {
        $path = $this->getAttribute($column);
        if (! $path) return null;
        $file = public_path($path);
        if (! is_file($file)) return null;
        $mime = @mime_content_type($file) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($file));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-label-success',
            'in_progress' => 'bg-label-warning',
            'closed' => 'bg-label-secondary',
            'cancelled' => 'bg-label-danger',
            default => 'bg-label-info',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }
}
