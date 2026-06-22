<?php

use App\Modules\AiAssistant\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::post('/ai/ask', [AIController::class, 'ask'])->name('ai.ask');
