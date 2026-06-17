<?php

namespace App\Http\Controllers;

use App\Exports\HolidaySampleExport;
use App\Exports\HolidaysExport;
use App\Imports\HolidaysImport;
use App\Models\Holiday;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        // Check user role
        $isAdmin = auth()->user()->role === 'admin';
        $selectedYear = (int) ($request->year ?: now()->year);
        $selectedMonth = $request->filled('month') ? (int) $request->month : null;

        $query = Holiday::query()->whereNull('archived_at');

        $query->whereYear('date', $selectedYear);

        if ($selectedMonth) {
            $query->whereMonth('date', $selectedMonth);
        }

        $holidays = $query->orderBy('date', 'asc')->get();
        $yearHolidays = Holiday::whereNull('archived_at')->whereYear('date', $selectedYear)->orderBy('date')->get();
        $stats = $this->buildStats($yearHolidays, $selectedYear);
        $archivedCount = Holiday::whereNotNull('archived_at')->count();

        return view('admin.holidays.index', compact('holidays', 'isAdmin', 'stats', 'yearHolidays', 'selectedYear', 'selectedMonth', 'archivedCount'));
    }

    public function create()
    {
        // Only admin can create
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to add holidays.');
        }

        $department = Department::get();
        $designations = Designation::get();
        return view('admin.holidays.create', compact('department', 'designations'));
    }

    public function store(Request $request)
    {
        // Only admin can store
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to add holidays.');
        }

        $request->validate([
            'date.*'                => 'required|date',
            'occassion.*'           => 'required|string|max:255',
            'department_id_json'    => 'nullable|array',
            'designation_id_json'   => 'nullable|array',
            'employment_type_json'  => 'nullable|array',
        ]);

        $groupId = (string) Str::uuid();
        $override = $request->boolean('override_holidays');

        foreach ($request->date as $index => $holidayDate) {
            $holidayData = [
                'date'                 => $holidayDate,
                'title'                => $request->occassion[$index],
                'occassion'            => $request->occassion[$index],
                'department_id_json'   => $request->department_id_json ? json_encode($request->department_id_json) : null,
                'designation_id_json'  => $request->designation_id_json ? json_encode($request->designation_id_json) : null,
                'employment_type_json' => $request->employment_type_json ? json_encode($request->employment_type_json) : null,
                'group_id'             => $groupId,
                'type'                 => 'holiday',
                'recurring_day'        => null
            ];

            if ($override) {
                $existing = Holiday::whereNull('archived_at')->whereDate('date', $holidayDate)->first();
                $existing ? $existing->update($holidayData) : Holiday::create($holidayData);
            } else {
                Holiday::create($holidayData);
            }
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
    }

    public function destroy(Holiday $holiday)
    {
        // Only admin can delete
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to delete holidays.');
        }

        return $this->archiveHoliday($holiday);
    }

    public function calendar(Request $request)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $selectedYear = (int) ($request->year ?: now()->year);

        // Get all holidays
        $holidays = Holiday::whereNull('archived_at')->whereYear('date', $selectedYear)->orderBy('date')->get();

        // Format for FullCalendar
        $events = [];

        foreach ($holidays as $holiday) {
            $event = [
                'id' => $holiday->id,
                'title' => $holiday->title,
                'start' => $holiday->date,
                'end' => $holiday->date,
                'color' => $this->getHolidayColor($holiday),
                'textColor' => '#fff',
                'allDay' => true,
                'extendedProps' => [
                    'description' => $holiday->occassion,
                    'type' => $holiday->type,
                    'date_label' => Carbon::parse($holiday->date)->format('l, d M Y'),
                ]
            ];

            // Only admin gets edit URL
            if ($isAdmin) {
                $event['url'] = route('holidays.edit', $holiday->id);
            }

            $events[] = $event;
        }

        return view('admin.holidays.calendar', [
            'holidays' => json_encode($events),
            'holidayRows' => $holidays,
            'isAdmin' => $isAdmin,
            'selectedYear' => $selectedYear,
            'stats' => $this->buildStats($holidays, $selectedYear),
        ]);
    }

    public function edit(Holiday $holiday)
    {
        // Only admin can edit
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to edit holidays.');
        }

        $department = Department::get();
        $designations = Designation::get();
        return view('admin.holidays.edit', compact('holiday', 'department', 'designations'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        // Only admin can update
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to update holidays.');
        }

        $request->validate([
            'date.*'                => 'required|date',
            'occassion.*'           => 'required|string|max:255',
            'department_id_json'    => 'nullable|array',
            'designation_id_json'   => 'nullable|array',
            'employment_type_json'  => 'nullable|array',
        ]);

        $groupId = $holiday->group_id ?? (string) Str::uuid();
        $existingHolidays = Holiday::where('group_id', $groupId)->get();
        $updatedIds = [];

        foreach ($request->date as $index => $holidayDate) {
            $holidayData = [
                'date'                 => $holidayDate,
                'title'                => $request->occassion[$index],
                'occassion'            => $request->occassion[$index],
                'department_id_json'   => $request->department_id_json ? json_encode($request->department_id_json) : null,
                'designation_id_json'  => $request->designation_id_json ? json_encode($request->designation_id_json) : null,
                'employment_type_json' => $request->employment_type_json ? json_encode($request->employment_type_json) : null,
                'group_id'             => $groupId,
                'type'                 => 'holiday',
                'recurring_day'        => null
            ];

            if (isset($existingHolidays[$index])) {
                $existingHolidays[$index]->update($holidayData);
                $updatedIds[] = $existingHolidays[$index]->id;
            } else {
                $new = Holiday::create($holidayData);
                $updatedIds[] = $new->id;
            }
        }

        // Delete holidays that were not in the submitted form
        Holiday::where('group_id', $groupId)
               ->whereNotIn('id', $updatedIds)
               ->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday(s) updated successfully');
    }

    public function employeeView(Request $request)
    {
        $selectedYear = (int) ($request->year ?: now()->year);
        $selectedMonth = $request->filled('month') ? (int) $request->month : null;

        $query = Holiday::query()->whereNull('archived_at')->whereYear('date', $selectedYear);

        if ($selectedMonth) {
            $query->whereMonth('date', $selectedMonth);
        }

        $holidays = $query->orderBy('date')->get();
        $yearHolidays = Holiday::whereNull('archived_at')->whereYear('date', $selectedYear)->orderBy('date')->get();
        $stats = $this->buildStats($yearHolidays, $selectedYear);
        $isAdmin = false;
        $archivedCount = 0;
        $holidayIndexRoute = 'employee.holidays';
        $holidayCalendarRoute = 'employee.holidays.calendar';

        return view('admin.holidays.index', compact(
            'holidays',
            'yearHolidays',
            'stats',
            'selectedYear',
            'selectedMonth',
            'isAdmin',
            'archivedCount',
            'holidayIndexRoute',
            'holidayCalendarRoute'
        ));
    }

    public function markHoliday(Request $request)
    {
        // Only admin can mark holidays
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('holidays.index')
                ->with('error', 'You are not authorized to mark holidays.');
        }

        $request->validate([
            'office_holiday_days' => 'nullable|array',
            'occassion'           => 'nullable|string|max:255',
            'date'                => 'nullable|date',
            'year'                => 'nullable|integer|min:2000|max:2100',
            'override_existing'   => 'nullable',
        ]);

        // Handle recurring weekly holidays
        if ($request->filled('office_holiday_days')) {
            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            $year = (int) ($request->year ?: now()->year);
            $startOfYear = Carbon::create($year)->startOfYear();
            $endOfYear   = Carbon::create($year)->endOfYear();
            $override = $request->boolean('override_existing', true);

            foreach ($request->office_holiday_days as $dayIndex) {
                $dayName = $days[$dayIndex];
                $current = $startOfYear->copy();
                if (strtolower($current->format('l')) !== $dayName) {
                    $current = $current->next($dayName);
                }

                while ($current->lte($endOfYear)) {
                    $holidayData = [
                        'group_id'            => uniqid(),
                        'title'               => $request->occassion ?: ucfirst($dayName),
                        'date'                => $current->format('Y-m-d'),
                        'occassion'           => $request->occassion ?: ucfirst($dayName),
                        'recurring_day'       => $dayName,
                        'department_id_json'  => null,
                        'designation_id_json' => null,
                        'employment_type_json'=> null,
                        'type'                => 'weekly_holiday'
                    ];

                    if ($override) {
                        $existing = Holiday::whereNull('archived_at')->whereDate('date', $current->format('Y-m-d'))->first();
                        $existing ? $existing->update($holidayData) : Holiday::create($holidayData);
                    } else {
                        $existing = Holiday::whereNull('archived_at')->whereDate('date', $current->format('Y-m-d'))->first();
                        if (! $existing) {
                            Holiday::create($holidayData);
                        }
                    }

                    $current->addWeek();
                }
            }
        }

        // Handle a one-time custom holiday
        if ($request->filled('occassion') && $request->filled('date')) {
            Holiday::create([
                'group_id'            => uniqid(),
                'title'               => $request->occassion,
                'date'                => $request->date,
                'occassion'           => $request->occassion,
                'recurring_day'       => null,
                'department_id_json'  => null,
                'designation_id_json' => null,
                'employment_type_json'=> null,
                'type'                => 'holiday'
            ]);
        }

        return redirect()->route('holidays.index')->with('success', 'Holiday(s) added successfully');
    }

    public function sample()
    {
        $this->ensureAdmin();

        return Excel::download(new HolidaySampleExport(), 'holiday-import-sample.xlsx');
    }

    public function export(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $month = $request->filled('month') ? (int) $request->month : null;
        $name = 'holiday-list' . ($year ? '-' . $year : '') . ($month ? '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) : '') . '.xlsx';

        return Excel::download(new HolidaysExport($year, $month), $name);
    }

    public function archive(Request $request)
    {
        $this->ensureAdmin();

        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $query = Holiday::whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('occassion', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhereDate('date', $search);
            });
        }

        $holidays = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

        return view('admin.holidays.archive', compact('holidays'));
    }

    public function archiveHoliday(Holiday $holiday)
    {
        $this->ensureAdmin();

        if ($holiday->archived_at) {
            return redirect()->route('holidays.index')->with('error', 'Holiday is already archived.');
        }

        $holiday->forceFill(['archived_at' => now()])->save();

        return redirect()->route('holidays.index')->with('success', 'Holiday archived successfully.');
    }

    public function restore($id)
    {
        $this->ensureAdmin();

        $holiday = Holiday::whereNotNull('archived_at')->findOrFail($id);
        $holiday->forceFill(['archived_at' => null])->save();

        return redirect()->route('holidays.archive')->with('success', 'Holiday restored successfully.');
    }

    public function bulkArchive(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'holiday_ids' => ['required', 'array'],
            'holiday_ids.*' => ['integer', 'exists:holidays,id'],
        ]);

        $count = Holiday::whereIn('id', $request->holiday_ids)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        return response()->json(['message' => $count . ' holiday(s) archived successfully.']);
    }

    public function importExcel(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'holiday_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new HolidaysImport();
        Excel::import($import, $request->file('holiday_file'));

        $message = "Holiday import complete. Created: {$import->created}, Updated: {$import->updated}, Skipped: {$import->skipped}.";

        return redirect()->route('holidays.index')
            ->with('success', $message)
            ->with('import_errors', array_slice($import->errors, 0, 8));
    }

    public function importImage(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'holiday_image' => ['required', 'image', 'max:6144'],
            'image_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'override_existing' => ['nullable'],
        ]);

        $text = $this->readImageText($request->file('holiday_image')->getRealPath());

        if (! $text) {
            return back()->with('error', 'The image was uploaded, but this server could not read text from it automatically. Install Tesseract OCR on the server, or use the Excel bulk upload template.');
        }

        $result = $this->importHolidaysFromText($text, (int) ($request->image_year ?: now()->year), $request->boolean('override_existing', true));

        return redirect()->route('holidays.index', ['year' => $request->image_year ?: now()->year])
            ->with('success', "Image holiday scan complete. Created: {$result['created']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}.")
            ->with('import_errors', array_slice($result['errors'], 0, 8));
    }

    public function bulkAction(Request $request)
    {
        // Only admin can perform bulk actions
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'error' => 'You are not authorized to perform this action.'
            ], 403);
        }

        $request->validate([
            'holiday_ids' => 'required|array',
            'action' => 'required|string|in:archive,delete,mark_active,mark_inactive',
        ]);

        $holidays = Holiday::whereIn('id', $request->holiday_ids);

        switch ($request->action) {
            case 'archive':
                $archived = (clone $holidays)->whereNull('archived_at')->update(['archived_at' => now()]);
                return response()->json(['message' => $archived . ' holiday(s) archived successfully.']);

            case 'delete':
                $holidays->delete();
                return response()->json(['message' => 'Selected holidays deleted successfully.']);

            case 'mark_active':
                $holidays->update(['notification_sent' => 1]);
                return response()->json(['message' => 'Selected holidays marked as active.']);

            case 'mark_inactive':
                $holidays->update(['notification_sent' => 0]);
                return response()->json(['message' => 'Selected holidays marked as inactive.']);

            default:
                return response()->json(['message' => 'Invalid action.'], 400);
        }
    }

    // Helper method for holiday colors
    private function getHolidayColor($holiday)
    {
        if ($holiday->type === 'weekly_holiday') {
            return '#28a745'; // Green for weekly holidays
        }

        return '#0d6efd'; // Blue for regular holidays
    }

    private function buildStats($holidays, int $year): array
    {
        return [
            'year' => $year,
            'total' => $holidays->count(),
            'special' => $holidays->where('type', '!=', 'weekly_holiday')->count(),
            'weekly' => $holidays->where('type', 'weekly_holiday')->count(),
            'months' => $holidays->groupBy(fn ($holiday) => Carbon::parse($holiday->date)->format('m'))->count(),
        ];
    }

    private function ensureAdmin(): void
    {
        abort_if(auth()->user()->role !== 'admin', 403);
    }

    private function readImageText(string $path): ?string
    {
        if (! function_exists('shell_exec')) {
            return null;
        }

        $version = @shell_exec('tesseract --version');
        if (! $version) {
            return null;
        }

        $command = 'tesseract ' . escapeshellarg($path) . ' stdout --psm 6 2>NUL';
        $text = @shell_exec($command);

        return trim((string) $text) ?: null;
    }

    private function importHolidaysFromText(string $text, int $year, bool $override): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach (preg_split('/\R+/', $text) as $lineNumber => $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if ($line === '') {
                continue;
            }

            $parsed = $this->parseHolidayLine($line, $year);
            if (! $parsed) {
                $skipped++;
                continue;
            }

            $payload = [
                'group_id' => (string) Str::uuid(),
                'title' => $parsed['occasion'],
                'date' => $parsed['date'],
                'occassion' => $parsed['occasion'],
                'type' => 'holiday',
                'recurring_day' => null,
            ];

            $existing = Holiday::whereNull('archived_at')->whereDate('date', $parsed['date'])->first();
            if ($existing && ! $override) {
                $skipped++;
                $errors[] = "Line " . ($lineNumber + 1) . ": {$parsed['date']} already exists.";
                continue;
            }

            if ($existing) {
                unset($payload['group_id']);
                $existing->update($payload);
                $updated++;
            } else {
                Holiday::create($payload);
                $created++;
            }
        }

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function parseHolidayLine(string $line, int $year): ?array
    {
        $patterns = [
            '/(?<date>\d{4}-\d{1,2}-\d{1,2})\s+[-:|]?\s*(?<name>.+)/',
            '/(?<date>\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})\s+[-:|]?\s*(?<name>.+)/',
            '/(?<date>\d{1,2}\s+[A-Za-z]{3,9})\s+[-:|]?\s*(?<name>.+)/',
            '/(?<name>.+?)\s+[-:|]?\s*(?<date>\d{1,2}\s+[A-Za-z]{3,9})$/',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $line, $matches)) {
                continue;
            }

            try {
                $rawDate = trim($matches['date']);
                if (preg_match('/^\d{1,2}\s+[A-Za-z]{3,9}$/', $rawDate)) {
                    $rawDate .= ' ' . $year;
                }

                return [
                    'date' => Carbon::parse($rawDate)->format('Y-m-d'),
                    'occasion' => trim($matches['name'], " \t\n\r\0\x0B-|:"),
                ];
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
