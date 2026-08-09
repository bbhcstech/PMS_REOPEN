<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends TenantModel
{
    protected $fillable = [
        'project_id', 'title', 'cost', 'status', 'add_to_budget',
        'summary', 'start_date', 'end_date'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}