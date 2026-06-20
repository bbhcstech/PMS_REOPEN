<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class EnterpriseCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['BIH', 'Bengal IT Hub', 'BIH', 'BIH-EMP', 'BIH-LV', 'BIH-PR', 'BIH-PS', 'Building the Future Together'],
            ['BBH', 'Biswa Bangla Hub', 'BBH', 'BBH-EMP', 'BBH-LV', 'BBH-PR', 'BBH-PS', 'Building the Future Together'],
            ['XC', 'Xink Careers', 'XC', 'XC-EMP', 'XC-LV', 'XC-PR', 'XC-PS', 'Empowering Careers'],
            ['XD', 'Xink Digitals', 'XD', 'XD-EMP', 'XD-LV', 'XD-PR', 'XD-PS', 'Digital Growth, Delivered'],
            ['XS', 'Xinksoft Pvt Limited', 'XS', 'XS-EMP', 'XS-LV', 'XS-PR', 'XS-PS', 'Engineering Better Work'],
        ];

        foreach ($companies as [$code, $name, $shortName, $employeePrefix, $leavePrefix, $payrollPrefix, $payslipPrefix, $greeting]) {
            Company::updateOrCreate(
                ['company_code' => $code],
                [
                    'name' => $name,
                    'short_name' => $shortName,
                    'email' => strtolower($code) . '@example.com',
                    'status' => 'active',
                    'employee_id_prefix' => $employeePrefix,
                    'leave_prefix' => $leavePrefix,
                    'payroll_prefix' => $payrollPrefix,
                    'payslip_prefix' => $payslipPrefix,
                    'greeting_message' => $greeting,
                    'max_users' => 1000,
                    'max_projects' => 1000,
                    'max_clients' => 1000,
                    'max_storage_mb' => 102400,
                ]
            );
        }
    }
}
