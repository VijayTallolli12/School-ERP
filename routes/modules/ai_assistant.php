<?php

use App\Modules\AiAssistant\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/dashboard', [AIController::class, 'dashboard'])
    ->middleware('role:Super Admin|School Admin|Principal|HR|Teacher|Accountant|Librarian|Payroll Manager|Receptionist|Staff')
    ->name('ai.dashboard');
Route::post('/ai/ask', [AIController::class, 'ask'])
    ->middleware('role:Super Admin|School Admin|Principal|HR|Teacher|Accountant|Librarian|Payroll Manager|Receptionist|Staff', 'throttle:ai')
    ->name('ai.ask');
