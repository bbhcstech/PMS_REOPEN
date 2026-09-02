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
            'tasks',
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

            return [
                'id' => $project->id,
                'project_id' => $project->id,
                'project' => $project,
                'order_no' => $project->project_code ?: ('ORD-' . date('Y') . '-' . str_pad($project->id, 4, '0', STR_PAD_LEFT)),
                'project_name' => $project->name,
                'customer_name' => $project->client?->name ?: 'Client #' . $project->client_id,
                'customer_email' => $project->client?->company_name ?: ($project->client?->email ?: 'client@example.com'),
                'items_count' => max(1, $project->tasks_count ?: $project->tasks->count()),
                'total_amount' => $totalAmount,
                'status' => $orderStatus,
                'raw_status' => $rawStatus,
                'payment_status' => $paymentStatus,
                'raw_payment_status' => strtolower(str_replace(' ', '_', $paymentStatus)),
                'created_at' => $dateFormatted,
                'deal_reference' => $matchedDeal ? ($matchedDeal->deal_name ?: $matchedDeal->product) : null,
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
}
