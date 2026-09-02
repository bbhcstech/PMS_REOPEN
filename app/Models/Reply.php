<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Reply extends TenantModel
{
    protected $fillable = ['ticket_id', 'user_id', 'message', 'attachment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
