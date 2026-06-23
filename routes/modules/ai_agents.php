<?php

use App\Modules\AiAgents\Controllers\AgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')
    ->name('agents.')
    ->group(function (): void {
        Route::get('/', [AgentController::class, 'index'])->name('index');
        Route::get('history', [AgentController::class, 'history'])->name('history');
        Route::get('history/data', [AgentController::class, 'historyData'])->name('history.data');
        Route::post('{agent}/preview', [AgentController::class, 'preview'])->name('preview');
        Route::post('{agent}/execute', [AgentController::class, 'execute'])->name('execute');
        Route::get('executions/{id}', [AgentController::class, 'executionDetail'])->name('executions.detail');
    });
