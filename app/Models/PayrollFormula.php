<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollFormula extends TenantModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'payroll_formulas';

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'formula',
        'variables_used',
        'test_inputs',
        'test_result',
        'is_valid',
        'is_active',
        'last_validated_at',
        'effective_from',
        'effective_to',
        'version',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'variables_used'   => 'array',
        'test_inputs'      => 'array',
        'test_result'      => 'decimal:2',
        'is_valid'         => 'boolean',
        'is_active'        => 'boolean',
        'last_validated_at'=> 'datetime',
        'effective_from'   => 'date',
        'effective_to'     => 'date',
        'version'          => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Evaluate a formula with provided variable bindings.
     * Uses the bounded arithmetic parser shared with the tester and payroll processor.
     * This compatibility helper returns false for invalid expressions.
     */
    public static function evaluate(string $formula, array $variables = []): float|false
    {
        try { return app(\App\Services\PayrollExpression::class)->evaluate($formula, $variables); }
        catch (\InvalidArgumentException $e) { return false; }
    }

    /**
     * Category label helper.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'earnings'  => 'Earnings',
            'deduction' => 'Deduction',
            'tax'       => 'Tax',
            'bonus'     => 'Bonus',
            default     => 'Custom',
        };
    }
}
