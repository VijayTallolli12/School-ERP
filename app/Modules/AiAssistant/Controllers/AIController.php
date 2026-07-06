<?php

namespace App\Modules\AiAssistant\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AiAssistant\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AIController extends Controller
{
    public function __construct(
        private readonly AIService $aiService
    ) {}

    public function dashboard(): View
    {
        return view('modules.ai-assistant.dashboard');
    }

    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'confirmed' => 'nullable|boolean',
        ]);

        $result = $this->aiService->ask(
            $request->input('question'),
            $request->boolean('confirmed', false)
        );

        return response()->json($result);
    }
}
