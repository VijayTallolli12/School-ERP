<?php

namespace App\Modules\AiAssistant\Services;

use App\Modules\AiAssistant\Handlers\AttendanceQueryHandler;
use App\Modules\AiAssistant\Handlers\FeeQueryHandler;
use App\Modules\AiAssistant\Handlers\LibraryQueryHandler;
use App\Modules\AiAssistant\Handlers\PayrollQueryHandler;
use App\Modules\AiAssistant\Handlers\StudentQueryHandler;
use App\Modules\AiAssistant\Handlers\TransportQueryHandler;

class AIService
{
    private array $handlers;

    public function __construct(
        private readonly IntentResolver $resolver,
        StudentQueryHandler $studentHandler,
        AttendanceQueryHandler $attendanceHandler,
        FeeQueryHandler $feeHandler,
        TransportQueryHandler $transportHandler,
        LibraryQueryHandler $libraryHandler,
        PayrollQueryHandler $payrollHandler,
    ) {
        $this->handlers = [
            'StudentQueryHandler' => $studentHandler,
            'AttendanceQueryHandler' => $attendanceHandler,
            'FeeQueryHandler' => $feeHandler,
            'TransportQueryHandler' => $transportHandler,
            'LibraryQueryHandler' => $libraryHandler,
            'PayrollQueryHandler' => $payrollHandler,
        ];
    }

    public function ask(string $question): array
    {
        $trimmed = trim($question);

        if (empty($trimmed)) {
            return [
                'success' => false,
                'answer' => 'Please enter a question.',
            ];
        }

        $intent = $this->resolver->resolve($trimmed);

        if (!$intent) {
            return [
                'success' => false,
                'answer' => "I couldn't understand your question. Try asking about:\n" . $this->getSupportedPreview(),
            ];
        }

        $handlerClass = $intent['handler'];
        $method = $intent['method'];

        if (!isset($this->handlers[$handlerClass])) {
            return [
                'success' => false,
                'answer' => 'Internal error: handler not found.',
            ];
        }

        $handler = $this->handlers[$handlerClass];

        if (!method_exists($handler, $method)) {
            return [
                'success' => false,
                'answer' => 'Internal error: query method not found.',
            ];
        }

        try {
            $answer = $handler->{$method}();
            return [
                'success' => true,
                'answer' => $answer,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'answer' => 'An error occurred while processing your request: ' . $e->getMessage(),
            ];
        }
    }

    private function getSupportedPreview(): string
    {
        $groups = $this->resolver->getSupportedQuestions();
        $lines = [];
        foreach ($groups as $category => $questions) {
            $lines[] = '• ' . ucfirst($category) . ': ' . implode(', ', array_slice($questions, 0, 3));
        }
        return implode("\n", $lines);
    }
}
