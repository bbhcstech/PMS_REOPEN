<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class TaskNote extends TenantModel
{
   protected $fillable = [
        'task_id',
        'user_id',
        'note',
        'added_by',
        'last_updated_by'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
