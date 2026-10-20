<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollFormula extends Model
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
     * Only numeric operations with whitelisted variables are allowed.
     */
    public static function evaluate(string $formula, array $variables = []): float|false
    {
        // Replace variable names with their values
        $expr = $formula;
        foreach ($variables as $key => $value) {
            $expr = str_replace(strtoupper($key), (float) $value, $expr);
        }

        // Allow only safe arithmetic: digits, decimals, operators, parentheses, whitespace
        if (!preg_match('/^[\d\s\+\-\*\/\.\(\)]+$/', $expr)) {
            return false;
        }

        try {
            // phpcs:ignore
            $result = eval("return ({$expr});");
            return is_numeric($result) ? (float) $result : false;
        } catch (\Throwable $e) {
            return false;
        }
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
