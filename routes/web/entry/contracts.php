<?php

use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ContractTemplateController;
use App\Http\Controllers\Admin\GovernmentIdVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('government-id-verifications', [GovernmentIdVerificationController::class, 'index'])->name('government-id-verifications.index');
    Route::patch('government-id-verifications/{verification}/approve', [GovernmentIdVerificationController::class, 'approve'])->name('government-id-verifications.approve');
    Route::patch('government-id-verifications/{verification}/reject', [GovernmentIdVerificationController::class, 'reject'])->name('government-id-verifications.reject');

    // Contracts
    Route::get('contracts/export', [ContractController::class, 'export'])->name('contracts.export');
    Route::post('contracts/{contract}/update-status', [ContractController::class, 'updateStatus'])->name('contracts.update-status');
    Route::post('contracts/{contract}/sign', [ContractController::class, 'signContract'])->name('contracts.sign');
    Route::get('contracts/by-client/{client}', [ContractController::class, 'getByClient'])->name('contracts.by-client');
    Route::resource('contracts', ContractController::class);

    // Contract Templates
    Route::post('contract-templates/{contractTemplate}/toggle-status', [ContractTemplateController::class, 'toggleStatus'])->name('contract-templates.toggle-status');
    Route::get('contract-templates/{contractTemplate}/content', [ContractTemplateController::class, 'getTemplateContent'])->name('contract-templates.content');
    Route::resource('contract-templates', ContractTemplateController::class);
});
