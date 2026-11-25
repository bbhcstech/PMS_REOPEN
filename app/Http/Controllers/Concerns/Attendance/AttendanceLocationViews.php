<?php

namespace App\Http\Controllers\Concerns\Attendance;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait AttendanceLocationViews
{
    /**
     * Location View - ADMIN ONLY
     */
    public function todayAttendanceByMap(Request $request)
    {
        $user = Auth::user();

        // Only authorized admin / management roles can access location view
        $userRole = strtolower((string) ($user->role ?? ''));
        if (! in_array($userRole, ['admin', 'superadmin', 'hr', 'manager', 'developer'], true)) {
            return redirect()->route('attendance.index')
                ->with('error', 'You do not have permission to access this page.');
        }

        $requestedDate = $request->input('date');
        $month = (int) ($request->input('month', now()->month));
        $year  = (int) ($request->input('year', now()->year));

        $query = Attendance::with([
            'user.employeeDetail.designation',
            'user.employeeDetail.department'
        ]);

        if ($requestedDate) {
            $query->whereDate('date', $requestedDate);
            $viewDate = $requestedDate;
        } elseif ($request->has('month') || $request->has('year')) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
            $viewDate = Carbon::createFromDate($year, $month, 1)->toDateString();
        } else {
            $viewDate = now()->toDateString();
            $query->whereDate('date', $viewDate);
        }

        $attendances = $query->get();

        // Append accessors and calculate total hours
        $attendances->each(function ($attendance) {
            $attendance->append(['total_seconds','total_duration','clock_in_datetime','clock_out_datetime']);

            // Calculate total hours if clock_in and clock_out exist
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $attendance->total_hours_calculated = $clockOut->diffInHours($clockIn);
            } else {
                $attendance->total_hours_calculated = null;
            }
        });

        // Enhanced debug data with both clock-in and clock-out info
        $debugData = $attendances->map(function ($att) {
            $user = $att->user;
            $empDetail = $user?->employeeDetail ?? null;

            // Check if location columns exist in database
            $hasClockInLocation = isset($att->clock_in_latitude) && $att->clock_in_latitude !== null;
            $hasClockOutLocation = isset($att->clock_out_latitude) && $att->clock_out_latitude !== null;

            return [
                'attendance_id' => $att->id,
                'user' => $user ? [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'employee_id' => $empDetail?->employee_id,
                    'avatar' => $user->profile_image_url ?? ($user->profile_image ?? null),
                    'email'  => $user->email,
                ] : null,
                'designation' => $empDetail?->designation?->name ?? null,
                'department'  => $empDetail?->department ? [
                    'id'   => $empDetail->department->id,
                    'name' => $empDetail->department->dpt_name ?? $empDetail->department->name ?? null,
                ] : null,
                // Clock-in data
                'clock_in_time' => $att->clock_in,
                'clock_in_latitude' => $hasClockInLocation ? $att->clock_in_latitude : null,
                'clock_in_longitude' => $hasClockInLocation ? $att->clock_in_longitude : null,
                'clock_in_address' => $hasClockInLocation ? ($att->clock_in_address ?? 'Coordinates recorded') : null,
                // Clock-out data
                'clock_out_time' => $att->clock_out,
                'clock_out_latitude' => $hasClockOutLocation ? $att->clock_out_latitude : null,
                'clock_out_longitude' => $hasClockOutLocation ? $att->clock_out_longitude : null,
                'clock_out_address' => $hasClockOutLocation ? ($att->clock_out_address ?? 'Coordinates recorded') : null,
                // Additional info
                'late' => in_array(strtolower((string)($att->late ?? '')), ['1', 'yes', 'true'], true),
                'status' => $att->status,
                'work_from_type' => $att->work_from_type ?? 'office',
                'working_from' => $att->working_from,
                'total_hours' => $att->total_hours_calculated ?? $att->total_hours ?? null,
                'date' => $att->date,
                'has_location_change' => $hasClockInLocation && $hasClockOutLocation &&
                    ($att->clock_in_latitude != $att->clock_out_latitude ||
                     $att->clock_in_longitude != $att->clock_out_longitude),
                'has_location_data' => $hasClockInLocation || $hasClockOutLocation,
            ];
        })->values();

        // Filter for map points (only show records with location data)
        $mapPoints = $debugData->filter(function ($a) {
            return !empty($a['user']) && $a['has_location_data'];
        })->values();

        $departments = $debugData->pluck('department')->filter()->unique('id')->values();

        $employees = User::where('role', 'employee')
            ->with(['employeeDetail.designation'])
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'employee_id' => $u->employeeDetail?->employee_id,
                'designation' => $u->employeeDetail?->designation?->name,
            ]);

        if ($departments->isEmpty()) {
            $departments = Department::query();
            if (\Illuminate\Support\Facades\Schema::hasColumn('departments', 'dpt_name')) {
                $departments->select('id', 'dpt_name as name');
            } else {
                $departments->select('id', 'name');
            }
            $departments = $departments->get();
        }

        return view('admin.attendance.by_map_location', [
            'attendanceData' => $mapPoints,
            'debugData'      => $debugData,
            'employees'      => $employees,
            'departments'    => $departments,
            'date'           => $viewDate,
            'month'          => $month,
            'year'           => $year,
        ]);
    }

    /**
     * Get employee locations - ROLE BASED (Admin can see all, employee only themselves)
     */
    public function getEmployeeLocations(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date'
            ]);

            $requestedUserId = $request->input('user_id');

            // Check permission: Employee can only request their own data
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own location data.'
                ], 403);
            }

            $date = $request->input('date');

            // Get attendance records for the specific date
            $attendances = Attendance::where('user_id', $requestedUserId)
                ->whereDate('date', $date)
                ->with(['user.employeeDetail.department', 'user.employeeDetail.designation'])
                ->orderBy('clock_in', 'asc')
                ->get();

            $selectedEmployee = User::with(['employeeDetail.designation', 'employeeDetail.department'])->find($requestedUserId);

            $attendanceData = $attendances->map(function ($record) {
                // Calculate total hours
                $totalHours = null;
                if ($record->clock_in && $record->clock_out) {
                    $clockIn = Carbon::parse($record->clock_in);
                    $clockOut = Carbon::parse($record->clock_out);
                    $totalHours = round($clockOut->diffInMinutes($clockIn) / 60, 2);
                }

                $hasClockInLocation = !empty($record->clock_in_latitude) && !empty($record->clock_in_longitude);
                $hasClockOutLocation = !empty($record->clock_out_latitude) && !empty($record->clock_out_longitude);
                $hasLocationChange = $hasClockInLocation && $hasClockOutLocation &&
                    ($record->clock_in_latitude != $record->clock_out_latitude ||
                     $record->clock_in_longitude != $record->clock_out_longitude);

                return [
                    'id' => $record->id,
                    'date' => $record->date,
                    'clock_in_time' => $record->clock_in ? Carbon::parse($record->clock_in)->format('h:i A') : null,
                    'clock_out_time' => $record->clock_out ? Carbon::parse($record->clock_out)->format('h:i A') : null,
                    'clock_in_latitude' => $hasClockInLocation ? (float)$record->clock_in_latitude : null,
                    'clock_in_longitude' => $hasClockInLocation ? (float)$record->clock_in_longitude : null,
                    'clock_in_address' => $hasClockInLocation ? ($record->clock_in_address ?? 'Location recorded') : null,
                    'clock_out_latitude' => $hasClockOutLocation ? (float)$record->clock_out_latitude : null,
                    'clock_out_longitude' => $hasClockOutLocation ? (float)$record->clock_out_longitude : null,
                    'clock_out_address' => $hasClockOutLocation ? ($record->clock_out_address ?? 'Location recorded') : null,
                    'late' => $record->late === 'yes',
                    'status' => $record->status,
                    'work_from_type' => $record->work_from_type ?? 'office',
                    'working_from' => $record->working_from,
                    'total_hours' => $totalHours,
                    'has_location_change' => $hasLocationChange,
                    'has_location_data' => $hasClockInLocation || $hasClockOutLocation,
                ];
            });

            return response()->json([
                'success' => true,
                'attendance' => $attendanceData,
                'employee' => $selectedEmployee ? [
                    'id' => $selectedEmployee->id,
                    'name' => $selectedEmployee->name,
                    'employee_id' => $selectedEmployee->employeeDetail?->employee_id,
                    'designation' => $selectedEmployee->employeeDetail?->designation?->name,
                ] : null,
                'date' => Carbon::parse($date)->format('F d, Y'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getEmployeeLocations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employee locations'
            ], 500);
        }
    }

    /**
     * Get employee timeline - ROLE BASED
     */
    public function getEmployeeTimeline(Request $request)
    {
        try {
            $user = Auth::user();
            $requestedUserId = $request->user_id;
            $date = $request->date;

            if (!$requestedUserId || !$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID and Date are required'
                ], 400);
            }

            // Check permission: Employee can only request their own timeline
            if ($user->role === 'employee' && $requestedUserId != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own timeline.'
                ], 403);
            }

            $userRecord = User::with(['employeeDetail.department', 'employeeDetail.designation'])->find($requestedUserId);

            if (!$userRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get attendance records
            $attendances = Attendance::where('user_id', $requestedUserId)
                ->whereDate('date', $date)
                ->orderBy('clock_in', 'asc')
                ->get();

            $activities = [];
            $totalHours = 0;

            foreach ($attendances as $index => $attendance) {
                $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('h:i A') : 'N/A';
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('h:i A') : 'N/A';

                // Calculate duration
                $duration = 0;
                if ($attendance->clock_in && $attendance->clock_out) {
                    $clockInTime = Carbon::parse($attendance->clock_in);
                    $clockOutTime = Carbon::parse($attendance->clock_out);
                    $duration = $clockOutTime->diffInHours($clockInTime);
                    $totalHours += $duration;
                }

                $activities[] = [
                    'index' => $index + 1,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'duration' => $duration > 0 ? number_format($duration, 2) . ' hours' : 'Not completed',
                    'work_mode' => $attendance->work_from_type ?? 'office',
                    'status' => $attendance->status,
                    'late' => $attendance->late === 'yes',
                    'clock_in_location' => $attendance->clock_in_address ?? 'No location data',
                    'clock_out_location' => $attendance->clock_out_address ?? 'No location data',
                ];
            }

            // Create simple HTML response
            $html = '<div class="timeline-container">';
            $html .= '<div class="summary-card mb-4">';
            $html .= '<div class="row">';
            $html .= '<div class="col-md-8">';
            $html .= '<h5 class="mb-3 fw-bold" style="color: #2c3e50;">';
            $html .= '<i class="fas fa-user me-2"></i>' . $userRecord->name . ' - Attendance Timeline';
            $html .= '</h5>';
            $html .= '<div class="d-flex flex-wrap gap-3">';
            $html .= '<div><span class="text-muted">Date:</span> <strong class="ms-2">' . Carbon::parse($date)->format('F d, Y') . '</strong></div>';
            $html .= '<div><span class="text-muted">Designation:</span> <strong class="ms-2">' . ($userRecord->employeeDetail?->designation?->name ?? 'N/A') . '</strong></div>';
            $html .= '<div><span class="text-muted">Department:</span> <strong class="ms-2">' . ($userRecord->employeeDetail?->department?->name ?? 'N/A') . '</strong></div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-md-4 text-end">';
            $html .= '<div class="bg-light p-3 rounded">';
            $html .= '<h6 class="mb-2">Total Hours Worked</h6>';
            $html .= '<h4 class="text-primary mb-0">' . number_format($totalHours, 2) . ' hours</h4>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';

            if (count($activities) > 0) {
                $html .= '<div class="timeline">';
                foreach ($activities as $activity) {
                    $html .= '<div class="timeline-item">';
                    $html .= '<div class="timeline-marker">';
                    $html .= '<div class="marker-icon ' . ($activity['late'] ? 'late' : 'present') . '"></div>';
                    $html .= '</div>';
                    $html .= '<div class="timeline-content">';
                    $html .= '<div class="timeline-header">';
                    $html .= '<div>';
                    $html .= '<h6 class="mb-1"><i class="fas fa-clock me-2"></i>Session ' . $activity['index'] . '</h6>';
                    $html .= '<div class="small text-muted">';
                    $html .= '<span class="me-3"><i class="fas fa-sign-in-alt me-1"></i>' . $activity['clock_in'] . '</span>';
                    $html .= '<span><i class="fas fa-sign-out-alt me-1"></i>' . $activity['clock_out'] . '</span>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div>';
                    $html .= '<span class="badge bg-' . ($activity['late'] ? 'warning' : 'success') . '">';
                    $html .= $activity['late'] ? 'Late' : 'On Time';
                    $html .= '</span>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="mt-3">';
                    $html .= '<div class="location-info">';
                    $html .= '<strong><i class="fas fa-map-marker-alt me-2"></i>Locations:</strong>';
                    $html .= '<div class="mt-2">';
                    $html .= '<div class="mb-1"><i class="fas fa-sign-in-alt text-success me-2"></i>' . $activity['clock_in_location'] . '</div>';
                    $html .= '<div><i class="fas fa-sign-out-alt text-primary me-2"></i>' . $activity['clock_out_location'] . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div class="mt-2 text-end">';
                    $html .= '<small class="text-muted">Duration: ' . $activity['duration'] . '</small>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div class="text-center py-5">';
                $html .= '<i class="fas fa-history fa-3x text-muted mb-3"></i>';
                $html .= '<p class="text-muted">No attendance records found for this date</p>';
                $html .= '</div>';
            }

            $html .= '</div>';

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getEmployeeTimeline: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching timeline details'
            ], 500);
        }
    }
}
