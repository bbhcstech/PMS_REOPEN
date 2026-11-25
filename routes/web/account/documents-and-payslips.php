<?php

use Illuminate\Support\Facades\Route;

Route::get('/my-documents', [\App\Http\Controllers\UserDocumentController::class, 'index'])->name('my-documents.index');
Route::post('/my-documents/upload', [\App\Http\Controllers\UserDocumentController::class, 'upload'])->name('my-documents.upload');
Route::delete('/my-documents/{type}/{id}', [\App\Http\Controllers\UserDocumentController::class, 'destroy'])->name('my-documents.destroy');
Route::get('/my-documents/download/{type}/{id}', [\App\Http\Controllers\UserDocumentController::class, 'download'])->name('my-documents.download');
Route::get('/my-documents/history/{type}/{id}', [\App\Http\Controllers\UserDocumentController::class, 'history'])->name('my-documents.history');

// My Payslips (HR, Manager, Employee and company roles: own released payslips only)
Route::get('/my-payslips', [\App\Http\Controllers\MyPayslipController::class, 'index'])->name('my-payslips.index');
Route::get('/my-payslips/{payslip}', [\App\Http\Controllers\MyPayslipController::class, 'show'])->name('my-payslips.show');
Route::get('/my-payslips/{payslip}/document', [\App\Http\Controllers\MyPayslipController::class, 'document'])->name('my-payslips.document');
Route::get('/my-payslips/{payslip}/pdf', [\App\Http\Controllers\MyPayslipController::class, 'pdf'])->name('my-payslips.pdf');
