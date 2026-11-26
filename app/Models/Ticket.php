<?php
namespace App\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Ticket extends TenantModel
{
    public const STATUSES = ['open' => 'Start', 'in_progress' => 'In progress', 'pending' => 'On hold', 'resolved' => 'Finished', 'closed' => 'Closed', 'reopened' => 'Reopened'];

    public static function statusLabel(?string $status): string
    {
        return self::STATUSES[$status] ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    protected static function booted(): void
    {
        static::addGlobalScope('ticket_company', function ($query) {
            $company = \App\Services\TenantScope::companyId();
            if (!$company) { $query->whereRaw('1 = 0'); return; }
            if ((new static)->getConnection()->getSchemaBuilder()->hasColumn('tickets', 'company_id')) {
                $query->where(function ($q) use ($company) {
                    $q->where('tickets.company_id', $company)->orWhere(function ($legacy) use ($company) {
                        $legacy->whereNull('tickets.company_id')->whereHas('requester', fn ($users) => $users->where('company_id', $company));
                    });
                });
            } else {
                $query->whereHas('requester', fn ($users) => $users->where('company_id', $company));
            }
        });
        static::creating(function ($ticket) {
            if ($ticket->getConnection()->getSchemaBuilder()->hasColumn('tickets', 'company_id')) {
                $ticket->company_id = \App\Services\TenantScope::companyId();
            }
        });
    }

    protected $fillable = [
        'requester_id',
        'requester_type',
        'requester_name',
        'group_id',
        'agent_id',
        'project_id',
        'affected_module',
        'type',
        'type_id',
        'subject',
        'description',
        'attachment',
        'priority',
        'deadline',
        'channel',
        'tags',
        'status'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function group()
    {
        return $this->belongsTo(TicketGroup::class, 'group_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id'); // or morphTo() if polymorphic
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class, 'ticket_id')->latest();
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
