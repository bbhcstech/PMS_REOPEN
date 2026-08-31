<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of client project orders fetched automatically from the projects section.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', 'all');

        // Fetch all active deals for automatic cost matching
        $allDeals = Deal::all();

        // Query all Client Projects from Projects section
        $query = Project::with([
            'client',
            'tasks.assignee',
            'tasks.category',
            'users',
            'expenses',
        ])
        ->withCount('tasks')
        ->whereNotNull('client_id')
        ->whereNotIn('project_type', ['home', 'internal', 'in_house'])
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

        $clientProjects = $query->get();

        // Map client projects into Order History records
        $orders = $clientProjects->map(function ($project) use ($allDeals) {
            $matchedDeal = $this->findMatchingDeal($project, $allDeals);
            
            // Calculate total amount / deal value
            if ($matchedDeal && !empty($matchedDeal->value) && (float) $matchedDeal->value > 0) {
                $totalAmount = (float) $matchedDeal->value;
                $costSource = 'deal';
            } elseif (!empty($project->project_budget) && (float) $project->project_budget > 0) {
                $totalAmount = (float) $project->project_budget;
                $costSource = 'budget';
            } elseif ($project->expenses->isNotEmpty() && $project->expenses->sum('price') > 0) {
                $totalAmount = (float) $project->expenses->sum('price');
                $costSource = 'expenses';
            } else {
                $totalAmount = 0.00;
                $costSource = 'none';
            }

            // Map status
            $rawStatus = strtolower(trim((string) $project->status));
            $orderStatus = match($rawStatus) {
                'completed' => 'Completed',
                'in progress', 'in_progress' => 'Processing',
                'pending', 'not started', 'not_started' => 'Pending',
                'canceled', 'cancelled', 'on hold', 'on_hold' => 'Cancelled',
                default => 'Processing'
            };

            // Map payment status from database column if explicitly set, otherwise fallback logically
            $dbPayment = strtolower(trim((string) $project->payment_status));
            if (!empty($dbPayment) && in_array($dbPayment, ['paid', 'unpaid', 'partially_paid', 'partial', 'refunded', 'pending'], true)) {
                $paymentStatus = match($dbPayment) {
                    'paid' => 'Paid',
                    'unpaid' => 'Unpaid',
                    'partially_paid', 'partial' => 'Partially Paid',
                    'refunded' => 'Refunded',
                    'pending' => 'Pending',
                    default => ucwords(str_replace('_', ' ', $dbPayment))
                };
            } else {
                $paymentStatus = match($orderStatus) {
                    'Completed' => 'Paid',
                    'Processing' => 'Paid',
                    'Pending' => 'Unpaid',
                    'Cancelled' => 'Refunded',
                    default => 'Paid'
                };
            }

            // Format date & time
            $dateFormatted = $project->created_at 
                ? $project->created_at->format('M d, Y H:i') 
                : ($project->start_date ? Carbon::parse($project->start_date)->format('M d, Y H:i') : now()->format('M d, Y H:i'));

            // Compile structured items
            $items = $this->compileOrderItems($project, $matchedDeal, $orderStatus, $totalAmount);
            $itemsCount = max(1, $project->tasks_count ?: $project->tasks->count());

            return [
                'id' => $project->id,
                'project_id' => $project->id,
                'project' => $project,
                'order_no' => $project->project_code ?: ('ORD-' . date('Y') . '-' . str_pad($project->id, 4, '0', STR_PAD_LEFT)),
                'project_name' => $project->name,
                'customer_name' => $project->client?->name ?: 'Client #' . $project->client_id,
                'customer_company' => $project->client?->company_name ?: '',
                'customer_email' => $project->client?->company_name ?: ($project->client?->email ?: 'client@example.com'),
                'items_count' => $itemsCount,
                'items' => $items,
                'has_real_tasks' => $project->tasks && $project->tasks->isNotEmpty(),
                'total_amount' => $totalAmount,
                'status' => $orderStatus,
                'raw_status' => $rawStatus,
                'payment_status' => $paymentStatus,
                'raw_payment_status' => strtolower(str_replace(' ', '_', $paymentStatus)),
                'created_at' => $dateFormatted,
                'deal_reference' => $matchedDeal ? ($matchedDeal->deal_name ?: $matchedDeal->product) : null,
                'create_task_url' => route('tasks.create', ['project_id' => $project->id]),
                'tasks_url' => route('tasks.index', ['project_id' => $project->id]),
                'project_url' => route('projects.show', $project->id),
            ];
        });

        // Apply status filter if selected
        if ($status && $status !== 'all') {
            $orders = $orders->filter(function ($item) use ($status) {
                return strtolower($item['status']) === strtolower($status);
            })->values();
        }

        // Calculate KPI Statistics based on all Client Projects
        $stats = [
            'total_orders' => $orders->count(),
            'completed' => $orders->where('status', 'Completed')->count(),
            'processing' => $orders->where('status', 'Processing')->count(),
            'pending' => $orders->where('status', 'Pending')->count(),
            'cancelled' => $orders->where('status', 'Cancelled')->count(),
            'total_revenue' => $orders->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'search', 'status'));
    }

    /**
     * Update payment status of a single project order (Ajax).
     */
    public function updatePaymentStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'payment_status' => 'required|string',
        ]);

        $project = Project::findOrFail($id);
        $normStatus = strtolower(str_replace([' ', '-'], '_', $request->payment_status));

        $validStatuses = ['paid', 'unpaid', 'partially_paid', 'partial', 'refunded', 'pending'];
        if (!in_array($normStatus, $validStatuses, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment status selected.',
            ], 422);
        }

        if ($normStatus === 'partial') {
            $normStatus = 'partially_paid';
        }

        $project->payment_status = $normStatus;
        $project->save();

        $label = match($normStatus) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'pending' => 'Pending',
            default => ucfirst($normStatus)
        };

        return response()->json([
            'success' => true,
            'message' => "Payment status updated to {$label} successfully.",
            'payment_status' => $label,
            'raw_status' => $normStatus,
        ]);
    }

    /**
     * Update payment status for multiple selected orders in bulk.
     */
    public function bulkUpdatePaymentStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:projects,id',
            'payment_status' => 'required|string',
        ]);

        $normStatus = strtolower(str_replace([' ', '-'], '_', $request->payment_status));
        if ($normStatus === 'partial') {
            $normStatus = 'partially_paid';
        }

        Project::whereIn('id', $request->ids)->update(['payment_status' => $normStatus]);

        $label = match($normStatus) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'refunded' => 'Refunded',
            'pending' => 'Pending',
            default => ucfirst($normStatus)
        };

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . " orders updated to {$label} successfully.",
            'payment_status' => $label,
            'raw_status' => $normStatus,
        ]);
    }

    /**
     * Match deal for a given project based on project name, product, or client details.
     */
    private function findMatchingDeal($project, $deals): ?Deal
    {
        $projectName = strtolower(trim((string) $project->name));
        $clientName = $project->client ? strtolower(trim((string) $project->client->name)) : '';
        $companyName = $project->client ? strtolower(trim((string) $project->client->company_name)) : '';

        // 1. Match deal name or product containing project name
        foreach ($deals as $deal) {
            $dName = strtolower(trim((string) $deal->deal_name));
            $dProd = strtolower(trim((string) $deal->product));
            if (!empty($projectName)) {
                if ($dName === $projectName || $dProd === $projectName || str_contains($dName, $projectName) || str_contains($projectName, $dName) || (!empty($dProd) && (str_contains($dProd, $projectName) || str_contains($projectName, $dProd)))) {
                    return $deal;
                }
            }
        }

        // 2. Match Client name or company in deal lead/contact
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

    /**
     * Get items table data for a specific order (Ajax / JSON).
     */
    public function getItems(Request $request, $id): JsonResponse
    {
        $project = Project::with([
            'client',
            'tasks.assignee',
            'tasks.category',
            'users',
            'expenses',
        ])
        ->whereNull('deleted_at')
        ->findOrFail($id);

        $allDeals = Deal::all();
        $matchedDeal = $this->findMatchingDeal($project, $allDeals);

        if ($matchedDeal && !empty($matchedDeal->value) && (float) $matchedDeal->value > 0) {
            $totalAmount = (float) $matchedDeal->value;
        } elseif (!empty($project->project_budget) && (float) $project->project_budget > 0) {
            $totalAmount = (float) $project->project_budget;
        } elseif ($project->expenses->isNotEmpty() && $project->expenses->sum('price') > 0) {
            $totalAmount = (float) $project->expenses->sum('price');
        } else {
            $totalAmount = 0.00;
        }

        $rawStatus = strtolower(trim((string) $project->status));
        $orderStatus = match($rawStatus) {
            'completed' => 'Completed',
            'in progress', 'in_progress' => 'Processing',
            'pending', 'not started', 'not_started' => 'Pending',
            'canceled', 'cancelled', 'on hold', 'on_hold' => 'Cancelled',
            default => 'Processing'
        };

        $dbPayment = strtolower(trim((string) $project->payment_status));
        if (!empty($dbPayment) && in_array($dbPayment, ['paid', 'unpaid', 'partially_paid', 'partial', 'refunded', 'pending'], true)) {
            $paymentStatus = match($dbPayment) {
                'paid' => 'Paid',
                'unpaid' => 'Unpaid',
                'partially_paid', 'partial' => 'Partially Paid',
                'refunded' => 'Refunded',
                'pending' => 'Pending',
                default => ucwords(str_replace('_', ' ', $dbPayment))
            };
        } else {
            $paymentStatus = match($orderStatus) {
                'Completed' => 'Paid',
                'Processing' => 'Paid',
                'Pending' => 'Unpaid',
                'Cancelled' => 'Refunded',
                default => 'Paid'
            };
        }

        $items = $this->compileOrderItems($project, $matchedDeal, $orderStatus, $totalAmount);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $project->id,
                'order_no' => $project->project_code ?: ('ORD-' . date('Y') . '-' . str_pad($project->id, 4, '0', STR_PAD_LEFT)),
                'project_name' => $project->name,
                'customer_name' => $project->client?->name ?: 'Client #' . $project->client_id,
                'customer_company' => $project->client?->company_name ?: '',
                'customer_email' => $project->client?->email ?: '',
                'total_amount' => $totalAmount,
                'formatted_amount' => '$' . number_format($totalAmount, 2),
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'items_count' => count($items),
                'has_real_tasks' => $project->tasks && $project->tasks->isNotEmpty(),
                'project_url' => route('projects.show', $project->id),
                'tasks_url' => route('tasks.index', ['project_id' => $project->id]),
                'create_task_url' => route('tasks.create', ['project_id' => $project->id]),
            ],
            'items' => $items,
        ]);
    }

    /**
     * Build structured items array for a project order.
     */
    private function compileOrderItems($project, $matchedDeal = null, string $orderStatus = 'Processing', float $totalAmount = 0.00): array
    {
        $items = [];
        $tasks = $project->tasks;

        if ($tasks && $tasks->isNotEmpty()) {
            foreach ($tasks as $index => $task) {
                $rawTaskStatus = strtolower(trim((string) $task->status));
                $taskStatus = match($rawTaskStatus) {
                    'completed' => 'Completed',
                    'in progress', 'in_progress', 'doing' => 'In Progress',
                    'pending', 'not started', 'not_started', 'to do' => 'Pending',
                    'on hold', 'on_hold', 'waiting' => 'On Hold',
                    'canceled', 'cancelled' => 'Cancelled',
                    default => !empty($task->status) ? ucwords(str_replace('_', ' ', $task->status)) : ($task->is_completed ? 'Completed' : 'Pending')
                };

                $priority = ucfirst(strtolower((string) ($task->priority ?: 'Medium')));
                $progress = (int) ($task->progress ?? ($task->is_completed ? 100 : 0));
                
                $dueFormatted = $task->due_date
                    ? Carbon::parse($task->due_date)->format('M d, Y')
                    : ($task->start_date ? Carbon::parse($task->start_date)->format('M d, Y') : 'No Deadline');

                $estHours = '-';
                if (!empty($task->estimate_hours) && (float) $task->estimate_hours > 0) {
                    $estHours = $task->estimate_hours . ' hrs';
                    if (!empty($task->estimate_minutes) && (int) $task->estimate_minutes > 0) {
                        $estHours .= ' ' . $task->estimate_minutes . 'm';
                    }
                } elseif (!empty($task->estimate_minutes) && (int) $task->estimate_minutes > 0) {
                    $estHours = $task->estimate_minutes . ' mins';
                }

                $assigneeName = $task->assignee?->name ?: 'Unassigned';
                $assigneeAvatar = $task->assignee?->image ? asset($task->assignee->image) : null;
                $categoryName = $task->category?->category_name ?: null;

                $items[] = [
                    'index' => $index + 1,
                    'id' => $task->id,
                    'type' => 'task',
                    'code' => $task->task_short_code ?: ('TSK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT)),
                    'title' => $task->title,
                    'description' => $task->description ?: ($categoryName ? "Category: {$categoryName}" : 'Project Task / Work Package'),
                    'category' => $categoryName,
                    'assignee_name' => $assigneeName,
                    'assignee_avatar' => $assigneeAvatar,
                    'priority' => $priority,
                    'status' => $taskStatus,
                    'progress' => $progress,
                    'due_date' => $dueFormatted,
                    'estimate_hours' => $estHours,
                    'billable' => $task->billable ? 'Billable' : 'Standard',
                    'is_completed' => (bool) $task->is_completed || $taskStatus === 'Completed',
                    'view_url' => route('tasks.show', $task->id),
                    'edit_url' => route('tasks.edit', $task->id),
                ];
            }
        } else {
            // Default primary package / service item if no discrete tasks yet
            $dealTitle = $matchedDeal ? ($matchedDeal->deal_name ?: $matchedDeal->product) : null;
            $itemTitle = $dealTitle ?: ($project->name ?: 'Client Project Scope & Deliverable');
            
            $dueFormatted = $project->deadline 
                ? Carbon::parse($project->deadline)->format('M d, Y') 
                : ($project->start_date ? Carbon::parse($project->start_date)->format('M d, Y') : 'Standard Timeline');

            $estHours = !empty($project->hours_allocated) ? ($project->hours_allocated . ' hrs') : '-';
            $firstUser = $project->users->first();

            $items[] = [
                'index' => 1,
                'id' => $project->id,
                'type' => 'package',
                'code' => $project->project_code ?: ('ITEM-' . str_pad($project->id, 4, '0', STR_PAD_LEFT)),
                'title' => $itemTitle,
                'description' => $project->description ?: 'Primary project package / service deliverable scope',
                'category' => 'Main Deliverable',
                'assignee_name' => $firstUser?->name ?: ($project->client?->name ?: 'Assigned Team'),
                'assignee_avatar' => $firstUser?->image ? asset($firstUser->image) : null,
                'priority' => ucfirst(strtolower((string) ($project->priority ?: 'Medium'))),
                'status' => $orderStatus,
                'progress' => (int) ($project->completion_percent ?? 0),
                'due_date' => $dueFormatted,
                'estimate_hours' => $estHours,
                'billable' => 'Billable',
                'is_completed' => ($orderStatus === 'Completed'),
                'view_url' => route('projects.show', $project->id),
                'edit_url' => route('projects.edit', $project->id),
            ];
        }

        return $items;
    }
}
