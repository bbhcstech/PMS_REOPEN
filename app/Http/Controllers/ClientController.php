<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientSubCategory;
use App\Models\ClientCategory;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectActivity;
use App\Models\ProjectUpdate;
use App\Models\Department;
use App\Models\ParentDepartment;
use App\Models\Currency;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\DealCategory;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        // base query
        $query = Client::query();

        // status filter: active|inactive|pending|all
        if ($request->filled('status') && $request->status !== 'all') {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        // name search
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // duration filter
        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // ignore invalid format
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate   = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate   = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate   = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = $endDate = null;
                }

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        $clients    = $query->latest()->paginate(25);
        $categories = ClientCategory::all();
        $countries  = Country::all();

        return view('admin.clients.index', compact('clients', 'categories', 'countries'));
    }   

    public function create() {
        $categories    = ClientCategory::all();
        $subcategories = ClientSubCategory::with('category')->get();
        $users         = User::all();
        $countries     = Country::all();

        // preview next client code (same logic as in Client model boot method)
        $nextId = (Client::max('id') ?? 0) + 1;
        $nextClientCode = 'XINK-CL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Project step data
        $projectCategories = ProjectCategory::all();
        $departments       = Department::with('parent')->latest()->get();
        $prtdepartments    = ParentDepartment::latest()->get();
        $currencies        = Currency::all();
        $employees         = User::where('role', 'employee')->orWhere('role', 'admin')->orderBy('name')->get();

        // preview next project code
        $now      = Carbon::now();
        $year     = (int) $now->format('Y');
        $fyStart  = $now->month >= 4 ? $year : $year - 1;
        $fyEnd    = $fyStart + 1;
        $fyString = substr($fyStart, -2) . '-' . substr($fyEnd, -2);
        $prefix   = 'bit' . $fyString . '/';

        $last = DB::table('projects')
            ->where('project_code', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->value('project_code');

        $lastNum = 0;
        if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
            $lastNum = (int) $m[1];
        }
        $nextProjectCode = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        // Deal step data
        $dealStages     = DealStage::all();
        $dealCategories = DealCategory::all();
        $dealAgents     = User::all();

        return view('admin.clients.create', compact(
            'categories',
            'subcategories',
            'users',
            'countries',
            'nextClientCode',
            'projectCategories',
            'departments',
            'prtdepartments',
            'currencies',
            'employees',
            'nextProjectCode',
            'dealStages',
            'dealCategories',
            'dealAgents'
        ));
    }

    public function store(Request $request)
    {
        Log::info('Client Form Data:', $request->all());

        // Normalize mobile
        if ($request->filled('mobile')) {
            $mobile = trim(preg_replace('/\s+/', '', (string) $request->mobile));
            if (!str_starts_with($mobile, '+91')) {
                $mobile = '+91' . ltrim($mobile, '+0');
            }
            $request->merge(['mobile' => $mobile]);
        }

        // Normalize office_phone (if just '+91' or empty, set to null)
        if ($request->filled('office_phone')) {
            $phone = trim(preg_replace('/\s+/', '', (string) $request->office_phone));
            if ($phone === '+91' || $phone === '') {
                $request->merge(['office_phone' => null]);
            } else {
                if (!str_starts_with($phone, '+91')) {
                    $phone = '+91' . ltrim($phone, '+0');
                }
                $request->merge(['office_phone' => $phone]);
            }
        } else {
            $request->merge(['office_phone' => null]);
        }

        $rules = [
            // Account Details
            'salutation'             => 'nullable|string|max:10',
            'name'                   => 'required|string|max:255',
            'email'                  => 'required|email|unique:clients,email',
            'password'               => 'nullable|string|min:6',
            'country'                => 'nullable',
            'mobile'                 => ['required', 'regex:/^\+91[0-9]{10}$/'],
            'profile_picture'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gender'                 => 'nullable',
            'language'               => 'nullable',
            'client_category_id'     => 'nullable|exists:client_categories,id',
            'client_sub_category_id' => 'nullable|exists:client_sub_categories,id',
            'login_allowed'          => 'nullable|boolean',
            'email_notifications'    => 'nullable|boolean',

            // Company Details
            'company_name'           => 'nullable|string|max:255',
            'website'                => 'nullable|url|max:255',
            'tax_name'               => 'nullable',
            'tax_number'             => 'nullable',
            'office_phone'           => ['nullable','regex:/^\+91[0-9]{10}$/'],
            'city'                   => 'nullable',
            'state'                  => 'nullable',
            'postal_code'            => 'nullable',
            'added_by'               => 'nullable|integer|exists:users,id',
            'company_address'        => 'nullable|string',
            'shipping_address'       => 'nullable|string',
            'note'                   => 'nullable|string',
            'company_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // Project Details (Optional)
            'project_name'           => 'nullable|string|max:255',
            'project_start_date'     => 'nullable|date',
            'project_deadline'       => 'nullable|date',
            'project_category_id'    => 'nullable',
            'project_priority'       => 'nullable|in:low,medium,high,critical',
            'project_budget'         => 'nullable|numeric',
            'project_hours'          => 'nullable|numeric',
            'completion_percent'     => 'nullable|integer|min:0|max:100',
            'project_department_ids' => 'nullable|array',
            'project_employee_ids'   => 'nullable|array',

            // Deal Details (Optional)
            'deal_name'              => 'nullable|string|max:255',
            'deal_value'             => 'nullable|numeric|min:0',
            'deal_close_date'        => 'nullable|date',
            'deal_stage_id'          => 'nullable|exists:deal_stages,id',
            'deal_category_id'       => 'nullable|exists:deal_categories,id',
            'deal_agent_id'          => 'nullable|exists:users,id',
        ];

        $request->validate($rules, [
            'mobile.regex'       => 'Mobile number must start with +91 and have 10 digits.',
            'office_phone.regex' => 'Office phone must start with +91 and have 10 digits.',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'salutation', 'name', 'email', 'country', 'mobile', 'gender', 'language',
                'client_category_id', 'client_sub_category_id', 'login_allowed', 'email_notifications',
                'company_name', 'website', 'tax_name', 'tax_number', 'office_phone', 'city',
                'state', 'postal_code', 'added_by', 'company_address', 'shipping_address', 'note'
            ]);

            // never allow client_uid from request (it will be auto generated in model)
            unset($data['client_uid']);

            // password handling (same for user and client)
            if ($request->filled('password')) {
                $password = Hash::make($request->input('password'));
            } else {
                // default password (optional – change as needed)
                $password = Hash::make('123456');
            }

            $data['password'] = $password;

            // profile picture upload
            $profileImagePath = null;
            $data['profile_picture'] = null;

            if ($request->hasFile('profile_picture')) {
                $image     = $request->file('profile_picture');
                $imageName = time() . '-' . $image->getClientOriginalName();
                $image->move(public_path('admin/uploads/clients-image'), $imageName);

                $profileImagePath            = 'admin/uploads/clients-image/' . $imageName;
                $data['profile_picture']     = $profileImagePath;
            }

            // create linked user
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'mobile'        => $request->mobile,
                'password'      => $password,
                'role'          => 'client',
                'profile_image' => $profileImagePath,
            ]);

            // company logo upload
            $data['company_logo'] = null;

            if ($request->hasFile('company_logo')) {
                $image     = $request->file('company_logo');
                $imageName = time() . '-' . $image->getClientOriginalName();
                $image->move(public_path('admin/uploads/clients-logo'), $imageName);

                $data['company_logo'] = 'admin/uploads/clients-logo/' . $imageName;
            }

            // create client (client_uid is auto-generated in Client model boot())
            $client = Client::create($data);

            // Step 3: Create Project if project name is filled
            if ($request->filled('project_name')) {
                $projectCode = null;
                if ($request->input('project_shortcode_option') === 'manual' && $request->filled('project_shortcode_manual')) {
                    $projectCode = $request->input('project_shortcode_manual');
                } else {
                    $now      = Carbon::now();
                    $year     = (int) $now->format('Y');
                    $fyStart  = $now->month >= 4 ? $year : $year - 1;
                    $fyEnd    = $fyStart + 1;
                    $fyString = substr($fyStart, -2) . '-' . substr($fyEnd, -2);
                    $prefix   = 'bit' . $fyString . '/';

                    $last = Project::where('project_code', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->value('project_code');

                    $lastNum = 0;
                    if ($last && preg_match('/\/(\d{4})$/', $last, $m)) {
                        $lastNum = (int) $m[1];
                    }
                    $projectCode = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
                }

                $categoryId = $request->filled('project_category_id') 
                    ? $request->project_category_id 
                    : (ProjectCategory::first()?->id ?? 1);

                $currencyId = $request->filled('project_currency_id') 
                    ? $request->project_currency_id 
                    : (Currency::where('currency_code', 'INR')->value('id') ?? Currency::first()?->id ?? 4);

                $authUserId = auth()->id() ?: (User::first()?->id ?? null);

                $project = Project::create([
                    'client_id'                 => $client->id,
                    'created_by'                => $authUserId,
                    'name'                      => $request->project_name,
                    'project_code'              => $projectCode,
                    'category_id'               => $categoryId,
                    'department_id'             => collect($request->input('project_department_ids', []))->first(),
                    'start_date'                => $request->project_start_date ?? Carbon::today(),
                    'deadline'                  => $request->boolean('project_without_deadline') ? null : $request->project_deadline,
                    'without_deadline'          => $request->boolean('project_without_deadline'),
                    'priority'                  => $request->input('project_priority', 'medium') ?: 'medium',
                    'currency_id'               => $currencyId,
                    'project_budget'            => $request->project_budget,
                    'hours_allocated'           => $request->project_hours,
                    'completion_percent'        => (int) $request->input('completion_percent', 0),
                    'description'               => $request->project_description,
                    'notes'                     => $request->project_notes,
                    'status'                    => $request->input('project_status', 'in progress') ?: 'in progress',
                    'public_gantt_chart'        => 'enable',
                    'public_taskboard'          => 'enable',
                    'client_access'             => 1,
                    'need_approval_by_admin'    => '0',
                    'calculate_task_progress'   => 'true',
                    'public'                    => '0',
                    'allow_client_notification' => 0,
                    'manual_timelog'            => 0,
                ]);

                if ($request->hasFile('project_file')) {
                    $file = $request->file('project_file');
                    $fileName = time() . '-' . $file->getClientOriginalName();
                    $file->move(public_path('admin/uploads/project-files'), $fileName);
                    $project->project_file = 'admin/uploads/project-files/' . $fileName;
                    $project->save();
                }

                // sync departments
                if ($request->has('project_department_ids')) {
                    $project->departments()->sync($request->input('project_department_ids', []));
                }

                // sync employee members
                $employeeIds = collect($request->input('project_employee_ids', []))->filter()->unique();
                if ($employeeIds->isNotEmpty()) {
                    $sync = [];
                    foreach ($employeeIds as $empId) {
                        $sync[$empId] = [
                            'assigned_by' => $authUserId,
                            'assigned_at' => now(),
                            'role'        => 'Project Member',
                        ];
                    }
                    $project->users()->sync($sync);
                }

                ProjectActivity::create([
                    'project_id' => $project->id,
                    'activity'   => (auth()->user()?->name ?? 'Admin') . ' created project: ' . $project->name . ' for client: ' . $client->name,
                ]);

                UserActivity::create([
                    'company_id' => auth()->user()->company_id ?? 1,
                    'user_id'    => $authUserId ?? 2,
                    'activity'   => 'Created a new project: ' . $project->name . ' for client: ' . $client->name,
                ]);

                ProjectUpdate::create([
                    'project_id'  => $project->id,
                    'employee_id' => auth()->user()?->role === 'employee' ? auth()->id() : null,
                    'status'      => $project->status,
                    'progress'    => (int) ($project->completion_percent ?? 0),
                    'remarks'     => $project->notes,
                    'updated_by'  => $authUserId,
                ]);
            }

            // Step 4: Create Deal if deal name is filled
            if ($request->filled('deal_name')) {
                Deal::create([
                    'deal_name'        => $request->deal_name,
                    'lead_name'        => $request->input('deal_lead_name') ?: $client->name,
                    'contact_details'  => $request->input('deal_contact_details') ?: ($client->email ?: ($client->mobile ?: 'N/A')),
                    'value'            => $request->input('deal_value', 0.00) ?: 0.00,
                    'close_date'       => $request->filled('deal_close_date') ? $request->deal_close_date : Carbon::today()->addDays(7),
                    'next_follow_up'   => $request->deal_next_follow_up,
                    'deal_stage_id'    => $request->filled('deal_stage_id') ? $request->deal_stage_id : (DealStage::first()?->id ?? 1),
                    'deal_category_id' => $request->deal_category_id,
                    'deal_agent_id'    => $request->deal_agent_id ?? $authUserId,
                    'pipeline'         => $request->input('deal_pipeline', 'Sales Pipeline') ?: 'Sales Pipeline',
                    'product'          => $request->deal_product,
                    'notes'            => $request->deal_notes,
                    'is_active'        => true,
                ]);
            }

            DB::commit();

            return redirect()->route('clients.index')->with('success', 'Client added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Client multi-step store failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create client: ' . $e->getMessage()]);
        }
    }

    public function edit(Client $client)
    {
        $categories    = ClientCategory::all();
        $subcategories = ClientSubCategory::with('category')->get();
        $users         = User::all();
        $countries     = Country::all();

        return view('admin.clients.edit', compact('client', 'categories', 'subcategories', 'users', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $user   = User::where('email', $client->email)->first();

     $request->validate([
    'salutation'             => 'nullable|string|max:10',
    'name'                   => 'required|string|max:255',
    'email'                  => 'required|email|unique:clients,email',
    'password'               => 'nullable|string|min:6',
    'country'                => 'nullable',
    'mobile'                 => 'nullable',
    'profile_picture'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    'gender'                 => 'nullable',
    'language'               => 'nullable',
    'client_category_id'     => 'nullable|exists:client_categories,id',
    'client_sub_category_id' => 'nullable|exists:client_sub_categories,id',
    'login_allowed'          => 'nullable|boolean',
    'email_notifications'    => 'nullable|boolean',
    'company_name'           => 'nullable|string|max:255',
    'website'                => 'nullable|url|max:255',
    'tax_name'               => 'nullable',
    'tax_number'             => 'nullable',
    'office_phone'           => ['nullable','regex:/^\+91[0-9]{10}$/'],
    'city'                   => 'nullable',
    'state'                  => 'nullable',
    'postal_code'            => 'nullable',
    'added_by'               => 'nullable|integer|exists:users,id',
    'company_address'        => 'nullable|string',
    'shipping_address'       => 'nullable|string',
    'note'                   => 'nullable|string',
    'company_logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
], [
    'office_phone.regex' => 'Office phone must start with +91 and have 10 digits.',
]);


        $data = $request->except(['password', 'profile_picture', 'company_logo']);

        // never allow client_uid to be updated via form
        unset($data['client_uid']);

        // password update
        if ($request->filled('password')) {
            $hashedPassword = Hash::make($request->password);
            if ($user) {
                $user->password = $hashedPassword;
            }
            $client->password = $hashedPassword;
        }

        // profile picture
        if ($request->hasFile('profile_picture')) {
            $image     = $request->file('profile_picture');
            $imageName = time() . '-' . $image->getClientOriginalName();
            $image->move(public_path('admin/uploads/clients-image'), $imageName);

            $data['profile_picture'] = 'admin/uploads/clients-image/' . $imageName;

            if ($user) {
                $user->profile_image = $data['profile_picture'];
            }
        }

        // company logo
        if ($request->hasFile('company_logo')) {
            $logo     = $request->file('company_logo');
            $logoName = time() . '-' . $logo->getClientOriginalName();
            $logo->move(public_path('admin/uploads/clients-logo'), $logoName);

            $data['company_logo'] = 'admin/uploads/clients-logo/' . $logoName;
        }

        // update client
        $client->update(array_merge($data, ['email' => $request->email]));

        // update user
        if ($user) {
            $user->name   = $request->name;
            $user->email  = $request->email;
            $user->mobile = $request->mobile;
            $user->save();
        }

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function show(Client $client)
    {
        $client->load(['category', 'subcategory', 'projects.users', 'projects.tasks', 'tickets']);
        return view('admin.clients.show', compact('client'));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'action'     => 'required|string',
        ]);

        $ids = $request->client_ids;

        if ($request->action === 'change-status' && $request->filled('status')) {
            Client::whereIn('id', $ids)
                ->update([
                    'login_allowed' => $request->status === 'Active' ? 1 : 0,
                    'status'        => $request->status,
                ]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        if ($request->action === 'delete') {
            Client::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Clients deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
    }

    public function pending(Request $request)
    {
        $query = Client::where('status', 'pending');

        // client filter (currently using id)
        if ($request->filled('name')) {
            $query->where('id', $request->name);
        }

        // duration filter
        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // skip invalid format
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate   = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate   = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate   = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate   = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = $endDate = null;
                }

                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        $clients    = $query->get();
        $categories = ClientCategory::all();
        $countries  = Country::all();

        return view('admin.clients.verification-pending', compact('clients', 'categories', 'countries'));
    }

    public function pendingbulkAction(Request $request)
    {
        $ids    = $request->client_ids;
        $action = $request->action;
        $status = $request->status;

        if ($action === 'change-status') {
            Client::whereIn('id', $ids)->update(['status' => $status]);
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        if ($action === 'delete') {
            Client::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Clients deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action']);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'client_ids'   => 'required|array',
            'client_ids.*' => 'integer|exists:clients,id',
        ]);

        $ids = $request->input('client_ids', []);
        Client::whereIn('id', $ids)->delete();

        return response()->json([
            'success'       => true,
            'message'       => 'Clients deleted successfully',
            'deleted_count' => count($ids),
        ]);
    }
}
