<?php

use App\Modules\AiAssistant\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/dashboard', [AIController::class, 'dashboard'])->name('ai.dashboard');
Route::post('/ai/ask', [AIController::class, 'ask'])->name('ai.ask');
