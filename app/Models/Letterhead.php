<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letterhead extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'letterheads';

    protected $fillable = [
        'name',
        'code',
        'type',
        'company_id',
        'branch_id',
        'department_id',
        'project_id',
        'company_name',
        'tagline',
        'branch_name',
        'department_name',
        'project_name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'alternate_phone',
        'email',
        'website',
        'registration_number',
        'tax_number',
        'gst_number',
        'cin_number',
        'other_info',
        'logo',
        'header_image',
        'footer_image',
        'background_page_image',
        'layout_mode',
        'content_padding_top',
        'content_padding_bottom',
        'content_padding_left',
        'content_padding_right',
        'preset_template',
        'logo_position',
        'logo_height',
        'header_content',
        'header_font',
        'header_font_size',
        'header_alignment',
        'header_border_style',
        'header_border_thickness',
        'header_border_color',
        'header_spacing',
        'header_height',
        'footer_content',
        'footer_text',
        'footer_font_size',
        'footer_alignment',
        'footer_border_style',
        'footer_border_thickness',
        'footer_border_color',
        'footer_spacing',
        'footer_height',
        'paper_size',
        'orientation',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'watermark_enabled',
        'watermark_type',
        'watermark_text',
        'watermark_image',
        'watermark_opacity',
        'watermark_rotation',
        'watermark_size',
        'primary_color',
        'secondary_color',
        'header_line_color',
        'footer_line_color',
        'status',
        'is_default',
        'version',
        'change_summary',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'watermark_enabled' => 'boolean',
        'is_default' => 'boolean',
        'watermark_opacity' => 'float',
        'logo_height' => 'integer',
        'header_font_size' => 'integer',
        'header_border_thickness' => 'integer',
        'header_spacing' => 'integer',
        'header_height' => 'integer',
        'footer_font_size' => 'integer',
        'footer_border_thickness' => 'integer',
        'footer_spacing' => 'integer',
        'footer_height' => 'integer',
        'margin_top' => 'integer',
        'margin_bottom' => 'integer',
        'margin_left' => 'integer',
        'margin_right' => 'integer',
        'content_padding_top' => 'integer',
        'content_padding_bottom' => 'integer',
        'content_padding_left' => 'integer',
        'content_padding_right' => 'integer',
        'watermark_rotation' => 'integer',
        'watermark_size' => 'integer',
        'version' => 'integer',
    ];

    /**
     * Determine if letterhead utilizes full A4 background page image.
     */
    public function hasFullPageBackground(): bool
    {
        return $this->layout_mode === 'full_a4_page' && ! empty($this->background_page_image);
    }

    /**
     * Determine if letterhead utilizes custom header and footer image/pdf assets.
     */
    public function hasCustomHeaderFooter(): bool
    {
        return $this->layout_mode === 'custom_header_footer' && (! empty($this->header_image) || ! empty($this->footer_image));
    }

    /**
     * Get the associated company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the associated branch location.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(BusinessAddress::class, 'branch_id');
    }

    /**
     * Get the associated department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the associated project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get creator user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get last updater user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Returns full formatted address.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Returns organization context display name.
     */
    public function getOrganizationDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'branch' => $this->branch?->display_name ?: ($this->branch_name ?: 'Branch Location'),
            'department' => $this->department?->dpt_name ?: ($this->department_name ?: 'Department'),
            'project' => $this->project?->name ?: ($this->project_name ?: 'Project'),
            'custom' => $this->company_name ?: 'Custom Organization',
            default => $this->company?->name ?: ($this->company_name ?: 'Main Company'),
        };
    }

    /**
     * Helper to get badge styling class for type.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'company' => 'bg-emerald-subtle text-emerald border-emerald',
            'branch' => 'bg-blue-subtle text-blue border-blue',
            'department' => 'bg-purple-subtle text-purple border-purple',
            'project' => 'bg-amber-subtle text-amber border-amber',
            default => 'bg-slate-subtle text-slate border-slate',
        };
    }

    /**
     * Helper to get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'lh-status-active',
            'draft' => 'lh-status-draft',
            'inactive' => 'lh-status-inactive',
            'archived' => 'lh-status-archived',
            default => 'lh-status-draft',
        };
    }
}
