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
        $query = Attendance::withoutGlobalScopes()->whereNotNull('clock_in')->whereNull('clock_out')
            ->whereHas('user', fn ($q) => $q->where('company_id', $companyId));
        if ($schema->hasColumn('attendances', 'company_id')) $query->where(fn ($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'));
        if ($schema->hasColumn('attendances', 'archived_at')) $query->whereNull('archived_at');
        if ($userId !== null) $query->where('user_id', $userId);
        $closed = 0;
        $query->chunkById(100, function ($rows) use ($now, &$closed) {
            foreach ($rows as $row) {
                DB::connection('tenant')->transaction(function () use ($row, $now, &$closed) {
                    $record = Attendance::withoutGlobalScopes()->whereKey($row->id)->lockForUpdate()->first();
                    if (!$record || $record->clock_out !== null) return;
                    $cutoff = $record->auto_clock_out_at;
                    if (!$cutoff || ($now ?: Carbon::now($cutoff->timezone))->lt($cutoff)) return;
                    $record->clock_out = $cutoff->format('H:i:s');
                    $schema = $record->getConnection()->getSchemaBuilder();
                    if ($schema->hasColumn('attendances', 'auto_clocked_out')) $record->auto_clocked_out = true;
                    if ($schema->hasColumn('attendances', 'total_hours')) $record->total_hours = round($record->total_seconds / 3600, 2);
                    $record->save();
                    WorkScheduleService::applyOrganizationAttendanceRules($record);
                    $closed++;
                });
            }
        });
        return $closed;
    }
}
