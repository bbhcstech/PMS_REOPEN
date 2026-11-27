<?php

use App\Http\Controllers\Admin\LetterheadController;
use App\Http\Controllers\Admin\LetterheadSignatureController;
use Illuminate\Support\Facades\Route;

Route::get('/letterhead', [LetterheadController::class, 'index'])->name('letterhead.index');
Route::get('/letterhead/create', [LetterheadController::class, 'create'])->name('letterhead.create');
Route::post('/letterhead', [LetterheadController::class, 'store'])->name('letterhead.store');
Route::post('/letterhead/placement/upload', [LetterheadController::class, 'savePlacementUpload'])->name('letterhead.placement.upload');
Route::delete('/letterhead/placement/remove', [LetterheadController::class, 'removePlacementUpload'])->name('letterhead.placement.remove');

// Authorized Electronic Signatures
Route::get('/letterhead/signatures', [LetterheadSignatureController::class, 'index'])->name('letterhead.signatures.index');
Route::post('/letterhead/signatures', [LetterheadSignatureController::class, 'store'])->name('letterhead.signatures.store');
Route::put('/letterhead/signatures/{signatureId}', [LetterheadSignatureController::class, 'update'])->name('letterhead.signatures.update');
Route::post('/letterhead/signatures/{signatureId}/default', [LetterheadSignatureController::class, 'setDefault'])->name('letterhead.signatures.default');
Route::post('/letterhead/signatures/{signatureId}/toggle-active', [LetterheadSignatureController::class, 'toggleActive'])->name('letterhead.signatures.toggle');
Route::delete('/letterhead/signatures/{signatureId}', [LetterheadSignatureController::class, 'destroy'])->name('letterhead.signatures.destroy');
Route::get('/letterhead/signatures/{signatureId}/image', [LetterheadSignatureController::class, 'image'])->name('letterhead.signatures.image');
Route::post('/letterhead/export-pdf', [LetterheadController::class, 'exportPdf'])->name('letterhead.export-pdf');
Route::post('/letterhead/export-word', [LetterheadController::class, 'exportWord'])->name('letterhead.export-word');
Route::get('/letterhead/demo-download', [LetterheadController::class, 'demoDownload'])->name('letterhead.demo-download');
Route::post('/letterhead/send-letter', [LetterheadController::class, 'sendLetter'])->name('letterhead.send-letter');
Route::get('/letterhead/sent-letters/{id}/pdf', [LetterheadController::class, 'downloadSentLetter'])->name('letterhead.sent-letters.pdf');
Route::get('/letterhead/{letterhead}', [LetterheadController::class, 'show'])->name('letterhead.show');
Route::get('/letterhead/{letterhead}/edit', [LetterheadController::class, 'edit'])->name('letterhead.edit');
Route::put('/letterhead/{letterhead}', [LetterheadController::class, 'update'])->name('letterhead.update');
Route::delete('/letterhead/{letterhead}', [LetterheadController::class, 'destroy'])->name('letterhead.destroy');
Route::post('/letterhead/{letterhead}/duplicate', [LetterheadController::class, 'duplicate'])->name('letterhead.duplicate');
Route::post('/letterhead/{letterhead}/default', [LetterheadController::class, 'setDefault'])->name('letterhead.default');
Route::post('/letterhead/{letterhead}/toggle-status', [LetterheadController::class, 'toggleStatus'])->name('letterhead.toggle-status');
Route::post('/letterhead/{letterhead}/archive', [LetterheadController::class, 'archive'])->name('letterhead.archive');
Route::get('/letterhead/{letterhead}/pdf', [LetterheadController::class, 'generatePdf'])->name('letterhead.pdf');
Route::get('/letterhead/{letterhead}/print', [LetterheadController::class, 'printPreview'])->name('letterhead.print');

// Legacy direct alias routes
Route::post('/letterhead/{company}/upload', [LetterheadController::class, 'upload'])->name('letterhead.upload');
Route::get('/letterhead/{company}/download', [LetterheadController::class, 'download'])->name('letterhead.download');
Route::delete('/letterhead/{company}/delete', [LetterheadController::class, 'destroyLegacy'])->name('letterhead.delete');
