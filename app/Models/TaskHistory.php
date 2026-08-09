<?php

namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends TenantModel
{
    
    protected $table = 'task_history'; // your actual table name

    protected $fillable = [
        'task_id',
        'sub_task_id',
        'user_id',
        'details',
        'board_column_id'
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
