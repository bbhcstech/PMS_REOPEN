<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StickyNote extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'note_text',
        'colour',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

}
