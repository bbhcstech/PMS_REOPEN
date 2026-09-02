<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentView extends TenantModel
{
    protected $table = 'document_views';

    protected $fillable = [
        'document_table',
        'document_id',
        'viewed_by_user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewed_by_user_id');
    }
}
