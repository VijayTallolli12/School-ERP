<?php

use App\Modules\Payroll\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll')
    ->name('payroll.')
    ->middleware('permission:payroll.view')
    ->group(function (): void {
        Route::get('/', [PayrollController::class, 'index'])->name('index');

        // Departments
        Route::get('departments/data', [PayrollController::class, 'departmentsData'])->name('departments.data');
        Route::post('departments', [PayrollController::class, 'storeDepartment'])->middleware('permission:payroll.create')->name('departments.store');
        Route::get('departments/{department}', [PayrollController::class, 'showDepartment'])->name('departments.show');
        Route::put('departments/{department}', [PayrollController::class, 'updateDepartment'])->middleware('permission:payroll.update')->name('departments.update');
        Route::delete('departments/{department}', [PayrollController::class, 'destroyDepartment'])->middleware('permission:payroll.delete')->name('departments.destroy');

        // Designations
        Route::get('designations/data', [PayrollController::class, 'designationsData'])->name('designations.data');
        Route::post('designations', [PayrollController::class, 'storeDesignation'])->middleware('permission:payroll.create')->name('designations.store');
        Route::get('designations/{designation}', [PayrollController::class, 'showDesignation'])->name('designations.show');
        Route::put('designations/{designation}', [PayrollController::class, 'updateDesignation'])->middleware('permission:payroll.update')->name('designations.update');
        Route::delete('designations/{designation}', [PayrollController::class, 'destroyDesignation'])->middleware('permission:payroll.delete')->name('designations.destroy');

        // Salary Components
        Route::get('salary-components/data', [PayrollController::class, 'salaryComponentsData'])->name('salary-components.data');
        Route::post('salary-components', [PayrollController::class, 'storeSalaryComponent'])->middleware('permission:payroll.create')->name('salary-components.store');
        Route::get('salary-components/{salaryComponent}', [PayrollController::class, 'showSalaryComponent'])->name('salary-components.show');
        Route::put('salary-components/{salaryComponent}', [PayrollController::class, 'updateSalaryComponent'])->middleware('permission:payroll.update')->name('salary-components.update');
        Route::delete('salary-components/{salaryComponent}', [PayrollController::class, 'destroySalaryComponent'])->middleware('permission:payroll.delete')->name('salary-components.destroy');

        // Pay Grades
        Route::get('pay-grades/data', [PayrollController::class, 'payGradesData'])->name('pay-grades.data');
        Route::post('pay-grades', [PayrollController::class, 'storePayGrade'])->middleware('permission:payroll.create')->name('pay-grades.store');
        Route::get('pay-grades/{payGrade}', [PayrollController::class, 'showPayGrade'])->name('pay-grades.show');
        Route::put('pay-grades/{payGrade}', [PayrollController::class, 'updatePayGrade'])->middleware('permission:payroll.update')->name('pay-grades.update');
        Route::delete('pay-grades/{payGrade}', [PayrollController::class, 'destroyPayGrade'])->middleware('permission:payroll.delete')->name('pay-grades.destroy');

        // Salary Structures
        Route::get('salary-structures/data', [PayrollController::class, 'salaryStructuresData'])->name('salary-structures.data');
        Route::post('salary-structures', [PayrollController::class, 'storeSalaryStructure'])->middleware('permission:payroll.create')->name('salary-structures.store');
        Route::get('salary-structures/{salaryStructure}', [PayrollController::class, 'showSalaryStructure'])->name('salary-structures.show');
        Route::put('salary-structures/{salaryStructure}', [PayrollController::class, 'updateSalaryStructure'])->middleware('permission:payroll.update')->name('salary-structures.update');
        Route::delete('salary-structures/{salaryStructure}', [PayrollController::class, 'destroySalaryStructure'])->middleware('permission:payroll.delete')->name('salary-structures.destroy');

        // Payroll Runs (Processing)
        Route::get('runs/data', [PayrollController::class, 'payrollRunsData'])->name('runs.data');
        Route::post('runs/generate', [PayrollController::class, 'generatePayroll'])->middleware('permission:payroll.process')->name('runs.generate');
        Route::get('runs/{payrollRun}', [PayrollController::class, 'showPayrollRun'])->name('runs.show');
        Route::post('runs/{payrollRun}/lock', [PayrollController::class, 'lockPayrollRun'])->middleware('permission:payroll.lock')->name('runs.lock');
        Route::delete('runs/{payrollRun}', [PayrollController::class, 'destroyPayrollRun'])->middleware('permission:payroll.delete')->name('runs.destroy');
        Route::get('runs/{runId}/items/data', [PayrollController::class, 'payRunItemsData'])->name('runs.items.data');

        // Reports
        Route::get('reports', [PayrollController::class, 'reports'])->name('reports.index');
        Route::get('reports/departments/data', [PayrollController::class, 'departmentsReportData'])->name('reports.departments.data');
        Route::get('reports/designations/data', [PayrollController::class, 'designationsReportData'])->name('reports.designations.data');
        Route::get('reports/salary-components/data', [PayrollController::class, 'salaryComponentsReportData'])->name('reports.salary-components.data');
        Route::get('reports/pay-grades/data', [PayrollController::class, 'payGradesReportData'])->name('reports.pay-grades.data');
        Route::get('reports/salary-structures/data', [PayrollController::class, 'salaryStructuresReportData'])->name('reports.salary-structures.data');
        Route::get('reports/employee-list/data', [PayrollController::class, 'employeeListReportData'])->name('reports.employee-list.data');
        Route::get('reports/run-summary/data', [PayrollController::class, 'runSummaryReportData'])->name('reports.run-summary.data');
        Route::get('reports/employee-payroll/data', [PayrollController::class, 'employeePayrollReportData'])->name('reports.employee-payroll.data');
        Route::get('reports/gross-vs-net/data', [PayrollController::class, 'grossVsNetReportData'])->name('reports.gross-vs-net.data');

        Route::get('reports/{report}/export/excel', [PayrollController::class, 'exportExcel'])->middleware('permission:payroll.export')->name('reports.export.excel');
        Route::get('reports/{report}/export/pdf', [PayrollController::class, 'exportPdf'])->middleware('permission:payroll.export')->name('reports.export.pdf');
        Route::get('reports/{report}/print', [PayrollController::class, 'printReport'])->middleware('permission:payroll.export')->name('reports.print');
    });
