<?php

use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('payroll')->name('payroll.')->group(function () {
    // 1. Dashboard
    Route::get('/', [PayrollController::class, 'index'])->name('index');

    // 2. Salary Structures
    Route::get('/salary-structures', [PayrollController::class, 'salaryStructures'])->name('salary-structures.index');
    Route::post('/salary-structures', [PayrollController::class, 'storeSalaryStructure'])->name('salary-structures.store');
    Route::put('/salary-structures/{structure}', [PayrollController::class, 'updateSalaryStructure'])->name('salary-structures.update');
    Route::delete('/salary-structures/{structure}', [PayrollController::class, 'destroySalaryStructure'])->name('salary-structures.destroy');

    // 3. Employee Salary Assignment
    Route::get('/employee-salary', [PayrollController::class, 'employeeSalary'])->name('employee-salary.index');
    Route::post('/employee-salary', [PayrollController::class, 'storeEmployeeSalary'])->name('employee-salary.store');
    Route::put('/employee-salary/{assignment}', [PayrollController::class, 'updateEmployeeSalary'])->name('employee-salary.update');
    Route::delete('/employee-salary/{assignment}', [PayrollController::class, 'destroyEmployeeSalary'])->name('employee-salary.destroy');
    Route::get('/employee-salary/defaults', [PayrollController::class, 'structureDefaults'])->name('employee-salary.defaults');

    // 4. Payroll Processing
    Route::get('/processing', [PayrollController::class, 'processing'])->name('processing');
    Route::post('/calculate', [PayrollController::class, 'calculate'])->name('calculate');
    Route::get('/preview/{history}', [PayrollController::class, 'preview'])->name('preview');
    Route::post('/line-input/{history}', [PayrollController::class, 'updateLineInput'])->name('line-input.update');
    Route::post('/{payroll}/review', [PayrollController::class, 'markReviewed'])->name('review');
    Route::post('/{payroll}/approve', [PayrollController::class, 'approve'])->name('approve');
    Route::post('/{payroll}/finalize', [PayrollController::class, 'finalize'])->name('finalize');
    Route::post('/{payroll}/recalculate', [PayrollController::class, 'recalculate'])->name('recalculate');
    Route::get('/{payroll}/export', [PayrollController::class, 'export'])->name('export');
    Route::post('/{payroll}/generate-payslips', [PayrollController::class, 'generatePayslipsForRun'])->name('generate-payslips');

    // 5. Payroll History
    Route::get('/history', [PayrollController::class, 'history'])->name('history');

    // 6. Payslips
    Route::get('/payslips', [PayrollController::class, 'payslips'])->name('payslips.index');
    Route::get('/payslips/{payslip}', [PayrollController::class, 'viewPayslip'])->name('payslips.view');
    Route::get('/payslips/{payslip}/print', [PayrollController::class, 'printPayslip'])->name('payslips.print');
    Route::get('/payslips/{payslip}/pdf', [PayrollController::class, 'downloadPdf'])->name('payslips.pdf');
    Route::post('/payslips/{payslip}/send', [PayrollController::class, 'sendPayslipSingle'])->name('payslips.send');
    Route::get('/payslip/{payslip}', [PayrollController::class, 'viewPayslip'])->name('payslip.view');
    Route::get('/payslip/{payslip}/print', [PayrollController::class, 'printPayslip'])->name('payslip.print');
    Route::get('/payslip/{payslip}/pdf', [PayrollController::class, 'downloadPdf'])->name('payslip.pdf');

    // 7. Formulas & Calculation Rules
    Route::get('/formulas', [PayrollController::class, 'formulas'])->name('formulas.index');
    Route::post('/formulas', [PayrollController::class, 'storeFormula'])->name('formulas.store');
    Route::put('/formulas/{formula}', [PayrollController::class, 'updateFormula'])->name('formulas.update');
    Route::delete('/formulas/{formula}', [PayrollController::class, 'destroyFormula'])->name('formulas.destroy');
    Route::post('/formulas/{formula}/restore', [PayrollController::class, 'restoreFormula'])->withTrashed()->name('formulas.restore');
    Route::post('/formulas/{formula}/toggle', [PayrollController::class, 'toggleFormulaActive'])->name('formulas.toggle');
    Route::post('/formulas/test', [PayrollController::class, 'testFormulaLive'])->name('formulas.test');

    // ── Legacy/Additional Support Routes ────────────────────────────────
    Route::get('/reports', [PayrollController::class, 'reports'])->name('reports.index');
    Route::get('/reports/export', [PayrollController::class, 'exportReport'])->name('reports.export');
    Route::get('/audit-logs', [PayrollController::class, 'auditLogs'])->name('audit-logs.index');
    Route::get('/policies', [\App\Http\Controllers\PayrollPolicyController::class, 'index'])->name('policies.index');
    Route::post('/policies', [\App\Http\Controllers\PayrollPolicyController::class, 'store'])->name('policies.store');
    Route::put('/policies/{policy}', [\App\Http\Controllers\PayrollPolicyController::class, 'update'])->name('policies.update');
    Route::get('/import-export', [PayrollController::class, 'importExport'])->name('import-export.index');
    Route::post('/import-export/import', [PayrollController::class, 'importPayroll'])->name('import-export.import');
    Route::get('/import-export/export-csv', [PayrollController::class, 'exportCsv'])->name('import-export.export-csv');
    Route::get('/archive', [PayrollController::class, 'archive'])->name('archive.index');
    Route::get('/settings', [PayrollController::class, 'settings'])->name('settings.index');
});
