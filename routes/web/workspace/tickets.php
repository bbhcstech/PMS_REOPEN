<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::resource('tickets', TicketController::class);
Route::post('/tickets/change-status', [TicketController::class, 'changeStatus'])->name('tickets.change-status');
Route::post('/ticket-groups/store', [TicketController::class, 'storeGroup'])->name('ticket-groups.store');
Route::get('/ticket-groups/fetch', [TicketController::class, 'fetchGroups'])->name('ticket-groups.fetch');
Route::delete('/ticket-groups/{id}', [TicketController::class, 'destroygroup'])->name('ticket-groups.destroy');
Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
Route::put('/tickets/{id}/update-details', [TicketController::class, 'updateDetails'])->name('tickets.updateDetails');
Route::post('/tickets/{id}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
Route::post('/tickets/{id}/log-time', [TicketController::class, 'logTime'])->name('tickets.logTime');
Route::post('tickets/bulk-action', [TicketController::class, 'bulkAction'])->name('tickets.bulk-action');
