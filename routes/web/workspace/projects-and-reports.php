<?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectNoteController;
use App\Http\Controllers\ProjectUserController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/projects/bulk-delete', [ProjectController::class, 'bulkDelete'])->name('projects.bulk-delete');
Route::post('/projects/bulk-status', [ProjectController::class, 'bulkStatus'])->name('projects.bulk-status');
Route::post('/projects/bulk-archive', [ProjectController::class, 'bulkArchive'])->name('projects.bulk-archive');
Route::post('/projects/import', [ProjectController::class, 'import'])->name('projects.import');
Route::patch('admin/projects/{project}/status', [ProjectController::class, 'toggleStatus'])->name('projects.toggleStatus');
Route::post('projects/{project}/updates', [ProjectController::class, 'storeUpdate'])->name('projects.updates.store');

Route::get('projects/archive', [ProjectController::class, 'archive'])->name('projects.archive');
Route::post('projects/{project}/archive', [ProjectController::class, 'archiveProject'])->name('projects.archive.action');

Route::get('projects/project-calendar', [ProjectController::class, 'projectCalendar'])->name('projects.calendar');
Route::put('projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
Route::delete('projects/{project}/force-delete', [ProjectController::class, 'forceDelete'])->name('projects.forceDelete');

Route::resource('projects', ProjectController::class);
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

// Project additional routes
Route::post('/project-categories', [ProjectController::class, 'categorystore'])->name('project-categories.store');
Route::delete('/project-categories/{id}', [ProjectController::class, 'categorydestroy'])->name('project-categories.destroy');
Route::post('/project/store', [ProjectController::class, 'clientstore'])->name('project.clientstore');
Route::post('/projects/{id}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');

// Archive-related
Route::get('projects/{project}/tasks/board', [TaskController::class, 'taskBoard'])->name('projects.tasks.board');

// Project members
Route::prefix('projects/{project}/members')->name('project-members.')->group(function () {
    Route::get('/', [ProjectUserController::class, 'index'])->name('index');
    Route::get('/add', [ProjectUserController::class, 'create'])->name('create');
    Route::post('/', [ProjectUserController::class, 'store'])->name('store');
    Route::delete('/{user}', [ProjectUserController::class, 'destroy'])->name('destroy');
});

// Project files
Route::prefix('projects/{project}/files')->name('project-files.')->group(function () {
    Route::get('/', [ProjectFileController::class, 'index'])->name('index');
    Route::post('/', [ProjectFileController::class, 'store'])->name('store');
    Route::delete('/{file}', [ProjectFileController::class, 'destroy'])->name('destroy');
});

// Project milestones
Route::get('/projects/{project}/milestones', [ProjectMilestoneController::class, 'index'])->name('milestones.index');
Route::post('/milestones/store', [ProjectMilestoneController::class, 'store'])->name('milestones.store');
Route::delete('/milestones/{id}', [ProjectMilestoneController::class, 'destroy'])->name('milestones.destroy');

// Project notes
Route::prefix('projects/{project}/notes')->name('projects.notes.')->group(function () {
    Route::get('/', [ProjectNoteController::class, 'index'])->name('index');
    Route::get('/create', [ProjectNoteController::class, 'create'])->name('create');
    Route::post('/', [ProjectNoteController::class, 'store'])->name('store');
    Route::get('{note}/view', [ProjectNoteController::class, 'show'])->name('noteshow');
    Route::get('{note}/edit', [ProjectNoteController::class, 'edit'])->name('edit');
    Route::put('{note}', [ProjectNoteController::class, 'update'])->name('update');
    Route::delete('{note}', [ProjectNoteController::class, 'destroy'])->name('destroy');
});

// Project discussions
Route::prefix('projects/{project}/discussions')->name('projects.discussions.')->group(function () {
    Route::get('/', [DiscussionController::class, 'index'])->name('index');
    Route::get('/create', [DiscussionController::class, 'create'])->name('create');
    Route::post('/', [DiscussionController::class, 'store'])->name('store');
    Route::get('{discussion}', [DiscussionController::class, 'show'])->name('show');
    Route::delete('{discussion}', [DiscussionController::class, 'destroy'])->name('destroy');
});

Route::post('/discussion-categories', [DiscussionController::class, 'disscatstore'])->name('discussion-categories.store');
Route::delete('/discussion-categories/{id}', [DiscussionController::class, 'disscatdestroy'])->name('discussion-categories.destroy');
Route::post('projects/{project}/discussions/{discussion}/replies', [DiscussionController::class, 'repliesstore'])->name('projects.discussions.replies.store');

// Project reports / advanced dashboards
Route::get('account/projects/{project}/burndown-chart', [ProjectController::class, 'burndown'])->name('projects.burndown');
Route::get('/admin/activity-log/project/{project}', [AdminActivityController::class, 'projectActivity'])->name('admin.activities.project');

// Comprehensive System Reports
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/task', [\App\Http\Controllers\ReportController::class, 'taskReport'])->name('task');
    Route::get('/timelog', [\App\Http\Controllers\ReportController::class, 'timelogReport'])->name('timelog');
    Route::get('/finance', [\App\Http\Controllers\ReportController::class, 'financeReport'])->name('finance');
    Route::get('/income-vs-expense', [\App\Http\Controllers\ReportController::class, 'incomeVsExpenseReport'])->name('income-vs-expense');
    Route::get('/expense', [\App\Http\Controllers\ReportController::class, 'expenseReport'])->name('expense');
    Route::get('/deal', [\App\Http\Controllers\ReportController::class, 'dealReport'])->name('deal');
    Route::get('/sales', [\App\Http\Controllers\ReportController::class, 'salesReport'])->name('sales');
});

Route::get('/account/dashboard-project', [DashboardController::class, 'project'])->name('dashproject');
Route::get('/account/dashboard-advanced', [DashboardController::class, 'clientDashboard'])->name('dashboard.client');
Route::get('/dashboard-advanced', [DashboardController::class, 'ticketDashboard'])->name('dashboard.ticket');
Route::get('/hr-dashboard', [DashboardController::class, 'hrindex'])->name('hr.dashboard');

// Gantt
Route::get('/projects/{project}/gantt', [ProjectController::class, 'ganttChart'])->name('projects.gantt');
Route::get('/projects/{project}/gantt-tasks', [ProjectController::class, 'getGanttTasks'])->name('projects.gantt-tasks');
Route::get('/projects/{project}/public-gantt', [ProjectController::class, 'publicGantt'])->name('projects.public-gantt');
