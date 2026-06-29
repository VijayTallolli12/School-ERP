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

    private const INTENT_AGENT_MAP = [
        'fee' => [
            'agent' => 'fee_collection',
            'label' => 'Fee Collection Agent',
            'params' => ['days' => 30],
        ],
        'attendance' => [
            'agent' => 'attendance',
            'label' => 'Attendance Agent',
            'params' => [],
        ],
        'library' => [
            'agent' => 'library',
            'label' => 'Library Agent',
            'params' => ['days' => 1],
        ],
        'payroll' => [
            'agent' => 'payroll',
            'label' => 'Payroll Agent',
            'params' => [],
        ],
    ];

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

        $intentKey = $this->resolver->resolveKey($trimmed);
        $intent = $intentKey ? $this->resolver->resolve($trimmed) : null;

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

            $response = [
                'success' => true,
                'answer' => $answer,
            ];

            $recommendation = $this->getAgentRecommendation($intentKey);
            if ($recommendation) {
                $response['agent_recommendation'] = $recommendation;
            }

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'answer' => 'An error occurred while processing your request: ' . $e->getMessage(),
            ];
        }
    }

    private function getAgentRecommendation(?string $intentKey): ?array
    {
        if (!$intentKey) {
            return null;
        }

        $category = explode('.', $intentKey)[0];

        if (!isset(self::INTENT_AGENT_MAP[$category])) {
            return null;
        }

        $map = self::INTENT_AGENT_MAP[$category];

        $params = $map['params'];

        if ($category === 'attendance') {
            $params['date'] = now()->format('Y-m-d');
        }

        if ($category === 'payroll') {
            $params['month'] = (int) now()->format('n');
            $params['year'] = (int) now()->format('Y');
        }

        return [
            'agent' => $map['agent'],
            'label' => $map['label'],
            'params' => $params,
        ];
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
