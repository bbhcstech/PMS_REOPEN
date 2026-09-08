<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\Expense;
use App\Models\Deal;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Task Report
     */
    public function taskReport(Request $request)
    {
        $query = Task::with(['project', 'category']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $tasks = $query->latest()->get();
        $projects = Project::orderBy('name')->get();

        $stats = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
        ];

        return view('admin.reports.task', compact('tasks', 'projects', 'stats'));
    }

    /**
     * Time Log Report
     */
    public function timelogReport(Request $request)
    {
        $query = TimeLog::with(['project', 'task', 'employee']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        $logs = $query->latest()->get();
        $employees = User::where('role', 'employee')->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        $totalHours = $logs->sum(function ($l) {
            return is_numeric($l->total_hours) ? abs((float)$l->total_hours) : 0;
        });

        return view('admin.reports.timelog', compact('logs', 'employees', 'projects', 'totalHours'));
    }

    /**
     * Finance Report
     */
    public function financeReport(Request $request)
    {
        $totalIncomes = Payment::sum('amount');
        $totalExpenses = \Illuminate\Support\Facades\Schema::hasColumn('expenses', 'status')
            ? Expense::where('status', 'approved')->sum('price')
            : Expense::sum('price');

        if ($totalExpenses == 0) {
            $totalExpenses = Expense::sum('price');
        }
        $totalInvoices = Invoice::sum('total');
        $netProfit = $totalIncomes - $totalExpenses;

        $payments = Payment::with(['project', 'invoice'])->latest()->take(50)->get();
        $expenses = Expense::with(['project', 'user'])->latest()->take(50)->get();

        return view('admin.reports.finance', compact('totalIncomes', 'totalExpenses', 'totalInvoices', 'netProfit', 'payments', 'expenses'));
    }

    /**
     * Income Vs Expense Report
     */
    public function incomeVsExpenseReport(Request $request)
    {
        $payments = Payment::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total_income')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $expenses = Expense::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(price) as total_expense')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $totalIncome = Payment::sum('amount');
        $totalExpense = Expense::sum('price');

        return view('admin.reports.income-vs-expense', compact('payments', 'expenses', 'totalIncome', 'totalExpense'));
    }

    /**
     * Expense Report
     */
    public function expenseReport(Request $request)
    {
        $query = Expense::with(['project', 'user']);

        if ($request->filled('status') && \Illuminate\Support\Facades\Schema::hasColumn('expenses', 'status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $expenses = $query->latest()->get();
        $totalAmount = $expenses->sum('price');

        return view('admin.reports.expense', compact('expenses', 'totalAmount'));
    }

    /**
     * Deal Report
     */
    public function dealReport(Request $request)
    {
        $deals = Deal::with(['category', 'stage', 'leadContact'])->latest()->get();
        if ($deals->isEmpty()) {
            $deals = Contract::with('client')->latest()->get();
        }

        $totalValue = $deals->sum('value') ?: $deals->sum('amount');

        return view('admin.reports.deal', compact('deals', 'totalValue'));
    }

    /**
     * Sales Report
     */
    public function salesReport(Request $request)
    {
        $query = Payment::with(['invoice', 'project']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $sales = $query->latest()->get();
        $totalSales = $sales->sum('amount');

        return view('admin.reports.sales', compact('sales', 'totalSales'));
    }
}
