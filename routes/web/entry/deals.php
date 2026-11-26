<?php

use App\Http\Controllers\DealController;
use Illuminate\Support\Facades\Route;

Route::get('admin/deals/index', [DealController::class, 'index'])->name('admin.deals.index');
Route::get('admin/deals/create', [DealController::class, 'create'])->name('admin.deals.create');
Route::get('admin/deals/export', [DealController::class, 'export'])->name('admin.deals.export');

// POST routes (no parameters)
Route::post('admin/deals', [DealController::class, 'store'])->name('admin.deals.store');
Route::post('admin/deals/import', [DealController::class, 'import'])->name('admin.deals.import');
Route::post('admin/deals/bulk-action', [DealController::class, 'bulkAction'])->name('admin.deals.bulk.action');

// Parameter routes - MUST be LAST
Route::get('admin/deals/{deal}', [DealController::class, 'show'])->name('admin.deals.show');
Route::get('admin/deals/{deal}/edit', [DealController::class, 'edit'])->name('admin.deals.edit');
Route::put('admin/deals/{deal}', [DealController::class, 'update'])->name('admin.deals.update');
Route::delete('admin/deals/{deal}', [DealController::class, 'destroy'])->name('admin.deals.destroy');
Route::post('admin/deals/{deal}/update-stage', [DealController::class, 'updateStage'])->name('admin.deals.update.stage');
Route::post('admin/deals/{deal}/lost-reason', [DealController::class, 'updateLostReason'])->name('admin.deals.update.lost-reason');
Route::post('admin/deals/{deal}/activities', [DealController::class, 'storeActivity'])->name('admin.deals.activities.store');
Route::post('admin/deals/{deal}/follow-ups', [DealController::class, 'storeFollowUp'])->name('admin.deals.follow-ups.store');
Route::post('admin/deals/{deal}/convert-to-client', [DealController::class, 'convertToClient'])->name('admin.deals.convert-to-client');
Route::post('/{deal}/add-follow-up', [DealController::class, 'addFollowUp'])->name('deals.add-follow-up');
