<?php

namespace Database\Seeders;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Database\Seeder;

class LeaveManagementSeeder extends Seeder
{
    public function run(): void
    {
        app(LeaveService::class)->ensureDefaultTypes();

        LeavePolicy::firstOrCreate([], [
            'annual_leaves' => 18,
            'sick_leave_limit' => 6,
            'casual_leave_limit' => 12,
            'maternity_leave_limit' => 0,
            'leave_year_start_month' => 4,
            'leave_year_end_month' => 3,
            'casual_advance_days' => 7,
            'casual_manual_review_days' => 2,
            'auto_approve_casual_leave' => false,
            'hr_approval_required' => true,
            'allow_sick_apology' => true,
            'allow_carry_forward' => false,
            'max_carry_forward' => 0,
            'unpaid_leave_handling' => 'unpaid_leave',
            'maternity_is_paid' => true,
            'maternity_requires_document' => true,
            'pro_rate_enabled' => false,
            'fiscal_year_start' => now()->month >= 4 ? now()->year . '-04-01' : (now()->year - 1) . '-04-01',
            'fiscal_year_end' => now()->month >= 4 ? (now()->year + 1) . '-03-31' : now()->year . '-03-31',
            'leave_monetary_value' => 0,
        ]);

        LeaveType::where('code', 'SL')->update(['annual_limit' => 6]);
        LeaveType::where('code', 'CL')->update(['annual_limit' => 12]);
        LeaveType::where('code', 'ML')->update(['annual_limit' => 0]);
    }
}
