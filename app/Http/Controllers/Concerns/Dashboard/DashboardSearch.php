<?php

namespace App\Http\Controllers\Concerns\Dashboard;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\Request;

trait DashboardSearch
{
    public function globalSearch(Request $request)
{
    $type = $request->input('type');
    $query = $request->input('query');
    $results = [];
    $companyId = app(\App\Services\CompanyContext::class)->id() ?? auth()->user()?->company_id;

    // Get matching results
    switch ($type) {
        case 'ticket':
            $results = Ticket::query()
                ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                ->where(function ($q) use ($query) {
                    $q->where('requester_name', 'like', "%$query%")
                      ->orWhere('requester_type', 'like', "%$query%")
                      ->orWhere('subject', 'like', "%$query%")
                      ->orWhere('description', 'like', "%$query%")
                      ->orWhere('attachment', 'like', "%$query%")
                      ->orWhere('priority', 'like', "%$query%")
                      ->orWhere('channel', 'like', "%$query%")
                      ->orWhere('tags', 'like', "%$query%")
                      ->orWhere('status', 'like', "%$query%");
                })->get();
            break;

        case 'task':
            $results = Task::query()
                ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                ->where(function ($q) use ($query) {
                    $q->where('task_short_code', 'like', "%$query%")
                      ->orWhere('title', 'like', "%$query%")
                      ->orWhere('description', 'like', "%$query%")
                      ->orWhere('assigned_to', 'like', "%$query%")
                      ->orWhere('task_labels', 'like', "%$query%")
                      ->orWhere('priority', 'like', "%$query%")
                      ->orWhere('status', 'like', "%$query%");
                })->get();
            break;

        case 'project':
            $results = Project::with('client')
                ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('projects', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                ->where(function ($rootQ) use ($query) {
                    $rootQ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%$query%")
                          ->orWhere('project_code', 'like', "%$query%")
                          ->orWhere('description', 'like', "%$query%")
                          ->orWhere('start_date', 'like', "%$query%")
                          ->orWhere('deadline', 'like', "%$query%")
                          ->orWhere('status', 'like', "%$query%");
                    })
                    ->orWhereHas('client', function ($q) use ($query) {
                        $q->where('name', 'like', "%$query%")
                          ->orWhere('email', 'like', "%$query%")
                          ->orWhere('mobile', 'like', "%$query%")
                          ->orWhere('company_name', 'like', "%$query%")
                          ->orWhere('website', 'like', "%$query%")
                          ->orWhere('country', 'like', "%$query%")
                          ->orWhere('city', 'like', "%$query%");
                    });
                })
                ->get();
            break;

        case 'employee':
            $empQuery = \DB::table('employee_details')
                ->join('users', 'employee_details.user_id', '=', 'users.id')
                ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
                ->select(
                    'employee_details.id as emp_detail_id',
                    'employee_details.employee_id',
                    'employee_details.status',
                    'employee_details.gender',
                    'employee_details.user_role',
                    'users.role',
                    'users.id as user_id',
                    'users.name',
                    'users.email',
                    'users.mobile',
                    'designations.name as designation'
                );

            if ($companyId && \Illuminate\Support\Facades\Schema::hasColumn('users', 'company_id')) {
                $empQuery->where(fn($sub) => $sub->where('users.company_id', $companyId)->orWhereNull('users.company_id'));
            }

            $results = $empQuery->where(function ($q) use ($query) {
                $q->where('users.name', 'like', "%{$query}%")
                  ->orWhere('users.email', 'like', "%{$query}%")
                  ->orWhere('users.mobile', 'like', "%{$query}%")
                  ->orWhere('users.role', 'like', "%{$query}%")
                  ->orWhere('designations.name', 'like', "%{$query}%")
                  ->orWhere('employee_details.employee_id', 'like', "%{$query}%")
                  ->orWhere('employee_details.status', 'like', "%{$query}%")
                  ->orWhere('employee_details.gender', 'like', "%{$query}%")
                  ->orWhere('employee_details.user_role', 'like', "%{$query}%");
            })->get();
            break;

        case 'client':
            $results = Client::query()
                ->when($companyId && \Illuminate\Support\Facades\Schema::hasColumn('clients', 'company_id'), fn ($q) => $q->where(fn($sub) => $sub->where('company_id', $companyId)->orWhereNull('company_id')))
                ->where('name', 'like', "%$query%")
                ->get();
            break;
    }

    // ✅ Redirect if only one match is found
    if ($results->count() === 1) {
        $single = $results->first();

        switch ($type) {
            case 'ticket':
                return redirect()->route('tickets.show', $single->id);
            case 'task':
                return redirect()->route('tasks.show', $single->id);
            case 'project':
                return redirect()->route('projects.show', $single->id);
            case 'employee':
                return redirect()->route('employees.show', $single->user_id);
            case 'client':
                return redirect()->route('clients.show', $single->id);
        }
    }

    // ✅ Otherwise, show result list
    return view('search-results', compact('results', 'type', 'query'));
}
}
