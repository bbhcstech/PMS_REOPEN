<?php

use App\Http\Controllers\Admin\LeadContactController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('leads/contacts', [LeadContactController::class, 'index'])->name('leads.contacts.index');
    Route::get('leads/contacts/create', [LeadContactController::class, 'create'])->name('leads.contacts.create');
    Route::post('leads/contacts/store', [LeadContactController::class, 'store'])->name('leads.contacts.store');
    Route::post('leads/contacts/check-duplicate', [LeadContactController::class, 'checkDuplicate'])->name('leads.contacts.check-duplicate');
    Route::get('leads/contacts/template', [LeadContactController::class, 'downloadTemplate'])->name('leads.contacts.template');
    Route::get('leads/contacts/{id}', [LeadContactController::class, 'show'])->name('leads.contacts.show');
    Route::get('leads/contacts/{id}/edit', [LeadContactController::class, 'edit'])->name('leads.contacts.edit');
    Route::put('leads/contacts/{id}', [LeadContactController::class, 'update'])->name('leads.contacts.update');
    Route::delete('leads/contacts/{id}', [LeadContactController::class, 'destroy'])->name('leads.contacts.destroy');
    Route::post('leads/contacts/{id}/activities', [LeadContactController::class, 'storeActivity'])->name('leads.contacts.activities.store');
    Route::post('leads/contacts/{id}/follow-ups', [LeadContactController::class, 'storeFollowUp'])->name('leads.contacts.follow-ups.store');

    // Bulk actions
    Route::post('/leads/contacts/bulk-delete', [LeadContactController::class, 'bulkDelete'])
        ->name('leads.contacts.bulk.delete');
    Route::post('leads/contacts/convert', [LeadContactController::class, 'convertToClient'])->name('leads.contacts.convert');

    // Import/Export
    Route::get('/leads/contacts/export', [LeadContactController::class, 'export'])->name('leads.contacts.export');
    Route::post('leads/contacts/import', [LeadContactController::class, 'import'])->name('leads.contacts.import');
});
