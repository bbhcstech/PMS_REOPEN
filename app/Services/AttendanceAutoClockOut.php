<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceAutoClockOut
{
    public function closeDue(int $companyId, ?int $userId = null, ?Carbon $now = null): int
    {
        $schema = Schema::connection('tenant');
        if (!$schema->hasTable('attendances') || !$schema->hasTable('users')) return 0;
        // MySQL ALTER TABLE implicitly commits. Initialize policy/schema before locking
        // attendance rows so a first-use upgrade cannot end the closing transaction.
        AttendanceClockPolicy::ensureSchema();
        $policy = WorkScheduleService::attendancePolicy();
        $columns = array_map('strtolower', $schema->getColumnListing('attendances'));
        $tracksAutoClockOut = in_array('auto_clocked_out', $columns, true);
        $tracksTotalHours = in_array('total_hours', $columns, true);
        $query = Attendance::withoutGlobalScopes()->whereNotNull('clock_in')->whereNull('clock_out')
            ->whereHas('user', fn ($q) => $q->where('company_id', $companyId));
        if (in_array('company_id', $columns, true)) $query->where(fn ($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'));
        if (in_array('archived_at', $columns, true)) $query->whereNull('archived_at');
        if ($userId !== null) $query->where('user_id', $userId);
        $closed = 0;
        $query->chunkById(100, function ($rows) use ($now, $policy, $tracksAutoClockOut, $tracksTotalHours, &$closed) {
            foreach ($rows as $row) {
                DB::connection('tenant')->transaction(function () use ($row, $now, $policy, $tracksAutoClockOut, $tracksTotalHours, &$closed) {
                    $record = Attendance::withoutGlobalScopes()->whereKey($row->id)->lockForUpdate()->first();
                    if (!$record || $record->clock_out !== null) return;
                    $cutoff = $record->auto_clock_out_at;
                    if (!$cutoff || ($now ?: Carbon::now($cutoff->timezone))->lt($cutoff)) return;
                    $record->clock_out = $cutoff->format('H:i:s');
                    if ($tracksAutoClockOut) $record->auto_clocked_out = true;
                    if ($tracksTotalHours) $record->total_hours = round($record->total_seconds / 3600, 2);
                    $record->save();
                    WorkScheduleService::applyOrganizationAttendanceRules($record, $policy);
                    $closed++;
                });
            }
        });
        return $closed;
    }
}
