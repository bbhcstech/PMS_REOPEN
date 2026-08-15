<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDocument extends TenantModel
{
    protected $table = 'hr_documents';

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function views()
    {
        return $this->hasMany(DocumentView::class, 'document_id')
            ->where('document_table', 'hr_documents')
            ->with('viewer')
            ->orderBy('viewed_at', 'desc');
    }

    public function getFileUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset($this->file_path);
    }
}
