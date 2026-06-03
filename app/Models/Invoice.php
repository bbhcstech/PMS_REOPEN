<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'company_id',
        'payment_id',
        'invoice_number',
        'amount',
        'tax',
        'total',
        'status',
        'due_date',
        'paid_at',
        'items',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
        'items' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
