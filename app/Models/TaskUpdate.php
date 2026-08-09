<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class TaskUpdate extends TenantModel
{
    protected $fillable = [
        'task_id',
        'user_id',
        'status',
        'progress',
        'remarks',
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
