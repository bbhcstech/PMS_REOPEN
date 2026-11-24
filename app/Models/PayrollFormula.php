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
     * Supports arithmetic (+, -, *, /), comparisons (>, <, >=, <=, ==, !=),
     * ternary conditionals (A ? B : C), and automatically handles trailing
     * semicolons and nested unparenthesized ternaries in PHP 8.
     */
    public static function evaluate(string $formula, array $variables = []): float|false
    {
        $expr = trim($formula);
        // 1. Strip trailing semicolons, commas, and whitespace
        $expr = rtrim($expr, ";, \t\n\r\0\x0B");

        if ($expr === '') {
            return false;
        }

        // 2. Sort variables by descending key length to prevent substring collisions (e.g. BASIC vs CURRENT_BASIC)
        uksort($variables, fn($a, $b) => strlen((string)$b) <=> strlen((string)$a));

        foreach ($variables as $key => $value) {
            $val = is_numeric($value) ? (float) $value : 0.0;
            // Use word boundary to replace exact variable token case-insensitively
            $expr = preg_replace('/\b' . preg_quote(strtoupper((string)$key), '/') . '\b/i', (string) $val, $expr);
        }

        // 3. Fix unparenthesized nested ternaries for PHP 8:
        // E.g. "A ? B : C ? D : E" -> "A ? B : (C ? D : E)"
        $count = 0;
        while (preg_match('/:\s*([^:\(\?]+)\s*\?\s*([^:]+)\s*:\s*(.+)$/', $expr) && $count < 10) {
            $expr = preg_replace('/:\s*([^:\(\?]+)\s*\?\s*([^:]+)\s*:\s*(.+)$/', ': ($1 ? $2 : $3)', $expr, 1);
            $count++;
        }

        // 4. Allow only safe arithmetic and conditional operations: digits, decimals, operators, comparisons, ternary, parentheses, whitespace
        if (!preg_match('/^[\d\s\+\-\*\/\.\(\)\?\:\>\<\=\!]+$/', $expr)) {
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
