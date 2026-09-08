<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of projects divided into Home Projects and Client Projects with Deal Costs.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');
        $activeTab = $request->get('tab', 'all'); // 'all', 'home', 'client'

        // Fetch all active deals for automatic cost matching
        $allDeals = Deal::with(['stage', 'category'])->get();

        // Query all projects with eager loading
        $query = Project::with([
            'client',
            'users.employeeDetail',
            'tasks',
            'expenses',
            'milestones',
            'latestUpdate',
        ])
        ->withCount('tasks')
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('company_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority && $priority !== 'all') {
            $query->where('priority', $priority);
        }

        $allProjects = $query->get();

        // Enrich each project with matched Deal Cost
        $allProjects->each(function ($project) use ($allDeals) {
            $matchedDeal = $this->findMatchingDeal($project, $allDeals);
            $project->matched_deal = $matchedDeal;
            
            if ($matchedDeal && !empty($matchedDeal->value) && (float) $matchedDeal->value > 0) {
                $project->calculated_cost = (float) $matchedDeal->value;
                $project->cost_source = 'deal';
                $project->deal_reference = $matchedDeal->deal_name ?: $matchedDeal->product;
            } elseif (!empty($project->project_budget) && (float) $project->project_budget > 0) {
                $project->calculated_cost = (float) $project->project_budget;
                $project->cost_source = 'budget';
                $project->deal_reference = 'Project Budget';
            } elseif ($project->expenses->isNotEmpty() && $project->expenses->sum('price') > 0) {
                $project->calculated_cost = (float) $project->expenses->sum('price');
                $project->cost_source = 'expenses';
                $project->deal_reference = 'Recorded Expenses';
            } else {
                $project->calculated_cost = 0.00;
                $project->cost_source = 'none';
                $project->deal_reference = null;
            }
        });

        // Divide projects into Home Projects and Client Projects
        $homeProjects = $allProjects->filter(function ($project) {
            $type = strtolower((string) $project->project_type);
            return empty($project->client_id) || $type === 'home' || $type === 'internal' || $type === 'in_house';
        })->values();

        $clientProjects = $allProjects->filter(function ($project) {
            $type = strtolower((string) $project->project_type);
            return !empty($project->client_id) && $type !== 'home' && $type !== 'internal' && $type !== 'in_house';
        })->values();

        // Calculate statistics
        $stats = [
            'total_projects' => $allProjects->count(),
            'home_projects_count' => $homeProjects->count(),
            'client_projects_count' => $clientProjects->count(),
            'total_deal_cost' => $allProjects->sum('calculated_cost'),
            'home_cost' => $homeProjects->sum('calculated_cost'),
            'client_cost' => $clientProjects->sum('calculated_cost'),
            'completed_count' => $allProjects->where('status', 'completed')->count(),
            'in_progress_count' => $allProjects->whereIn('status', ['in progress', 'in_progress'])->count(),
        ];

        return view('admin.products.index', compact(
            'homeProjects',
            'clientProjects',
            'stats',
            'search',
            'status',
            'priority',
            'activeTab'
        ));
    }

    /**
     * Find the best matching Deal for a Project based on name, product, or client.
     */
    private function findMatchingDeal($project, $deals): ?Deal
    {
        $projectName = strtolower(trim((string) $project->name));
        $clientName = $project->client ? strtolower(trim((string) $project->client->name)) : '';
        $companyName = $project->client ? strtolower(trim((string) $project->client->company_name)) : '';

        // 1. Direct match on deal_name or product containing project name
        foreach ($deals as $deal) {
            $dName = strtolower(trim((string) $deal->deal_name));
            $dProd = strtolower(trim((string) $deal->product));
            if (!empty($projectName)) {
                if ($dName === $projectName || $dProd === $projectName || str_contains($dName, $projectName) || str_contains($projectName, $dName) || (!empty($dProd) && (str_contains($dProd, $projectName) || str_contains($projectName, $dProd)))) {
                    return $deal;
                }
            }
        }

        // 2. Match on Client name or company in deal's lead/contact details
        if (!empty($clientName) || !empty($companyName)) {
            foreach ($deals as $deal) {
                $leadName = strtolower(trim((string) $deal->lead_name));
                $contact = strtolower(trim((string) $deal->contact_details));
                if ((!empty($clientName) && (str_contains($leadName, $clientName) || str_contains($contact, $clientName))) ||
                    (!empty($companyName) && (str_contains($leadName, $companyName) || str_contains($contact, $companyName)))) {
                    return $deal;
                }
            }
        }

        return null;
    }
}
