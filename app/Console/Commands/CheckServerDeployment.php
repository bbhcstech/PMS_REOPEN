<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Schema};

/** Read-only deployment checks for the failure families found in the supplied production log. */
class CheckServerDeployment extends Command
{
    protected $signature = 'server:check {--company= : Company ID, code or database to inspect} {--skip-database : Check code/configuration only}';
    protected $description = 'Check production dependencies, routes, logging, database access and company schema without changing records.';
    private int $failures = 0;

    private const TENANT_COLUMNS = [
        'users' => ['id', 'company_id', 'role', 'is_active', 'login_allowed', 'archived_at'],
        'notifications' => ['id', 'notifiable_id', 'notifiable_type', 'data', 'read_at'],
        'sticky_notes' => ['user_id', 'completed_at'],
        'countries' => ['phone_code', 'iso_code', 'min_digits', 'max_digits'],
        'employee_details' => ['user_id', 'company_id', 'parent_dpt_id', 'status', 'exit_date'],
        'departments' => ['dpt_name', 'dpt_code'],
        'designations' => ['name', 'parent_id', 'level'],
        'projects' => ['company_id', 'project_code', 'status', 'enable_miroboard', 'files'],
        'tasks' => ['project_id', 'parent_id', 'task_short_code', 'priority', 'task_labels', 'milestone_id', 'company_id'],
        'clients' => ['status', 'category_id', 'company_country'],
        'client_categories' => ['name'],
        'holidays' => ['date', 'occassion', 'group_id'],
        'awards' => ['appreciation_id'],
        'attendances' => ['user_id', 'date', 'clock_in', 'clock_out', 'location', 'clock_in_latitude', 'clock_in_longitude', 'clock_in_address', 'work_from_type', 'clock_in_photo', 'clock_out_photo', 'clock_in_timezone', 'system_status_reason'],
        'attendance_settings' => ['clock_window_enabled', 'clock_in_opens_at', 'clock_out_closes_at'],
        'employee_salary_assignments' => ['user_id', 'effective_from', 'effective_to', 'deleted_at'],
        'payroll_formulas' => ['effective_from', 'effective_to', 'deleted_at', 'is_active'],
        'payroll_audit_logs' => ['user_id'],
        'appraisals' => ['company_id', 'status', 'policy_snapshot'],
    ];

    private function check(bool $valid, string $label): void
    {
        $valid ? $this->line('OK: '.$label) : $this->error('FAIL: '.$label);
        if (!$valid) $this->failures++;
    }

    private function columns(string $connection, string $table, array $expected): void
    {
        $schema = Schema::connection($connection);
        if (!$schema->hasTable($table)) { $this->check(false, "$connection.$table is missing; run its migrations."); return; }
        $missing = array_diff($expected, $schema->getColumnListing($table));
        $this->check(!$missing, "$connection.$table".($missing ? ' missing: '.implode(', ', $missing) : ' schema'));
    }

    public function handle(): int
    {
        $this->failures = 0;
        foreach (['dom', 'gd', 'mbstring', 'zip', 'pdo_mysql'] as $extension) $this->check(extension_loaded($extension), 'PHP extension '.$extension);
        foreach ([\PhpOffice\PhpSpreadsheet\Reader\Csv::class, \Maatwebsite\Excel\Excel::class] as $class) $this->check(class_exists($class), 'Composer dependency '.$class);
        $channel = config('logging.default');
        $this->check(is_string($channel) && is_array(config('logging.channels.'.$channel)), 'Configured log channel exists');
        if ($channel === 'stack') {
            foreach (config('logging.channels.stack.channels', []) as $member) {
                $this->check(is_string($member) && is_array(config('logging.channels.'.$member)), 'Stack log channel '.$member.' exists');
            }
        }
        foreach (['dashboard', 'payroll.processing', 'clients.lookup-pincode', 'appraisal.index'] as $name) {
            $this->check(app('router')->getRoutes()->getByName($name) !== null, 'Route '.$name);
        }
        foreach (['storage/framework/views', 'storage/framework/cache', 'storage/logs', 'bootstrap/cache'] as $folder) {
            $this->check(is_dir(base_path($folder)) && is_writable(base_path($folder)), 'Writable '.$folder);
        }
        if ($this->option('skip-database')) return $this->failures ? self::FAILURE : self::SUCCESS;

        $original = config('database.connections.tenant');
        try {
            DB::connection('central')->getPdo();
            $this->columns('central', 'companies', ['id', 'name', 'db_name', 'settings', 'manually_suspended']);
            if (!Schema::connection('central')->hasTable('companies')) return self::FAILURE;
            if (config('session.driver') === 'database') {
                $this->columns(config('session.connection') ?: config('database.default'), config('session.table'), ['id', 'payload', 'last_activity']);
            }
            if (DB::connection('central')->getDriverName() === 'mysql') {
                $limit = DB::connection('central')->selectOne("SHOW VARIABLES LIKE 'max_user_connections'");
                $this->line('MySQL max_user_connections: '.($limit->Value ?? 'unknown').'. Hosting limits and PHP worker concurrency must be checked by the server administrator.');
            }
            $query = DB::connection('central')->table('companies')->whereNotNull('db_name');
            if (Schema::connection('central')->hasColumn('companies', 'deleted_at')) $query->whereNull('deleted_at');
            if ($target = $this->option('company')) {
                $query->where(fn ($q) => $q->where('id', $target)->orWhere('company_code', $target)->orWhere('db_name', $target));
            }
            $companies = $query->get(['id', 'db_name']);
            if ($this->option('company')) $this->check($companies->isNotEmpty(), 'Requested company is registered');
            foreach ($companies as $company) {
                $this->line('Company #'.$company->id);
                try {
                    if (!trim((string) $company->db_name)) throw new \RuntimeException('Empty company database');
                    config(['database.connections.tenant' => array_replace($original, ['database' => $company->db_name, 'url' => null])]);
                    DB::purge('tenant'); DB::connection('tenant')->getPdo();
                    $this->check(true, 'Registered company database is accessible');
                    foreach (self::TENANT_COLUMNS as $table => $columns) $this->columns('tenant', $table, $columns);
                } catch (\Throwable $e) {
                    $this->check(false, 'Company database access/schema check failed ('.get_class($e).'). Check database name, grants and connection limits.');
                } finally { DB::purge('tenant'); }
            }
        } catch (\Throwable $e) {
            $this->check(false, 'Registry/session database check failed ('.get_class($e).'). Check deployment configuration and database grants.');
        } finally {
            DB::purge('tenant'); config(['database.connections.tenant' => $original]);
        }
        $this->line('Failures: '.$this->failures.'. No data or schema was modified.');
        return $this->failures ? self::FAILURE : self::SUCCESS;
    }
}
