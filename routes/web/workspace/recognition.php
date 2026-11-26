<?php

use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;

Route::post('/awards/bulk-action', [AwardController::class, 'bulkAction'])->name('awards.bulkAction');
Route::post('/awards/bulk-delete', [AwardController::class, 'bulkDelete'])->name('awards.bulk-delete');
Route::post('/appreciations/{id}/status', [AwardController::class, 'updateStatus'])->name('appreciations.updateStatus');

// Appreciation routes with CORRECT spelling (double 'p')
Route::get('/awards/appreciation-index', [AwardController::class, 'appreciationIndex'])->name('awards.appreciation-index');
Route::get('/awards/appreciation-create', [AwardController::class, 'appreciationCreate'])->name('awards.appreciation-create');
Route::post('/awards/appreciation-store', [AwardController::class, 'appreciationStore'])->name('awards.appreciation-store');
Route::get('/awards/appreciation/edit/{id}', [AwardController::class, 'appreciationEdit'])->name('awards.appreciation-edit');
Route::put('/awards/appreciation/update/{id}', [AwardController::class, 'appreciationUpdate'])->name('awards.appreciation-update');
Route::delete('/awards/appreciation/{id}', [AwardController::class, 'appreciationDestroy'])->name('awards.appreciation-destroy');
Route::post('/awards/appreciation-bulk-action', [AwardController::class, 'appreciationBulkAction'])->name('awards.appreciation-bulk-action');

// Resource routes
Route::resource('awards', AwardController::class)->except(['show']);
Route::get('my-awards', [AwardController::class, 'myAwards'])->name('employee.awards');
Route::get('/my-awards', [AwardController::class, 'myAwards'])->name('awards.my-awards');
