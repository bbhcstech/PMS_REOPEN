<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TimeLogController;
use App\Models\Task;
use Illuminate\Support\Facades\Route;

Route::get('/get-subdepartments/{parentId}', function ($parentId) {
    return \App\Models\Department::where('parent_dpt_id', $parentId)->get();
})->name('get.subdepartments');





Route::get(
    '/employees/parent-departments/{id}/sub-departments',
    [EmployeeController::class, 'getSubDepartments']
)->name('employees.sub-departments');

Route::delete('/tickets/bulk-delete', [TicketController::class, 'bulkDelete'])
     ->name('tickets.bulk-delete');




Route::delete('timelogs/bulk-delete', [TimeLogController::class, 'bulkDelete'])
    ->name('timelogs.bulk-delete');


Route::get('/test-email', fn () => redirect()->route('admin.settings.email'))
    ->middleware(['auth', 'role:admin']);

Route::get('/debug-whos-logged', function () {
    $u = auth()->user();

    return response()->json([
        'auth_id' => $u?->id,
        'class'   => $u ? (method_exists($u, 'getMorphClass') ? $u->getMorphClass() : get_class($u)) : null,
        'email'   => $u?->email,
    ]);
})->middleware(['web', 'auth']);

Route::get('/debug-create-notif', function () {
    $u = auth()->user();
    if (! $u) {
        return response()->json(['error' => 'not logged in'], 401);
    }

    $task = Task::first() ?: Task::create([
        'task_short_code' => 'DBG1',
        'title'           => 'Debug Task',
        'project_id'      => 1,
    ]);

    $u->notify(new \App\Notifications\TaskAssignedNotification($task));

    return response()->json([
        'status'  => 'ok',
        'message' => 'notification created for current user',
        'user_id' => $u->id,
    ]);
})->middleware(['web', 'auth']);


// // Notification routes (works for both admin and employee)
// Route::middleware(['auth'])->group(function () {
//     Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
//     Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
//     Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
//     Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
//     Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
//     Route::get('/notifications/latest', [NotificationController::class, 'getLatest'])->name('notifications.latest');
// });

// Route::middleware(['auth'])->group(function () {
//     // 🔥 FIX: Remove duplicate route for /employee/notifications
//     // Keep only this one:
//     Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

//     Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
//     Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
//     Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
//     Route::delete('/notifications/delete/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');

//     // Send notification routes
//     Route::post('/notifications/admin-to-employees', [NotificationController::class, 'adminToEmployees'])->name('notifications.adminToEmployees');
//     Route::post('/notifications/employee-to-admins', [NotificationController::class, 'employeeToAdmins'])->name('notifications.employeeToAdmins');
//     Route::post('/notifications/send-to-users', [NotificationController::class, 'sendToUsers'])->name('notifications.sendToUsers');
// });

// Admin specific routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Your existing admin routes
    // Example: Route::get('/dashboard', ...)->name('admin.dashboard');
});

// Employee specific routes (without duplicate notifications route)
Route::middleware(['auth'])->prefix('employee')->group(function () {
    // Other employee routes
    // Example: Route::get('/dashboard', ...)->name('employee.dashboard');

    // 🔥 REMOVE THIS DUPLICATE LINE:
    // Route::get('/notifications', [NotificationController::class, 'index'])->name('employee.notifications.index');
});
