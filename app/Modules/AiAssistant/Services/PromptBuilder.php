<?php

namespace App\Modules\AiAssistant\Services;

class PromptBuilder
{
    private const PARAMETER_SCHEMA = 'Parameters: period, month(1-12), year, amount, limit, group_by(class|section|teacher|route|department), sort(asc|desc), date(YYYY-MM-DD), days, exam_id, route_id, student_ids, class_section_id, subject_id, title, message, target_type(students|teachers|parents|all), due_date(YYYY-MM-DD)';

    private array $modules;

    public function __construct(
        private readonly ContextBuilder $contextBuilder,
    ) {
        $this->modules = config('ai.modules', []);
    }

    public function buildModulePrompt(): string
    {
        $moduleLines = [];
        foreach ($this->modules as $name => $config) {
            $moduleLines[] = "- {$name}";
        }

        $moduleList = implode("\n", $moduleLines);
        $context = $this->contextBuilder->buildContext();

        $contextBlock = '';
        if ($context !== '') {
            $contextBlock = <<<CTX

School Context:
{$context}
CTX;
        }

        return <<<PROMPT
You are an ERP router.

Choose ONE module only.

Available modules:
{$moduleList}{$contextBlock}

Return JSON only.
{"module":"module_name","confidence":0.98}
PROMPT;
    }

    public function buildIntentPrompt(string $module): string
    {
        $config = $this->modules[$module] ?? null;

        if (!$config || empty($config['intents'])) {
            return $this->buildUnknownModulePrompt($module);
        }

        $intentLines = [];
        foreach ($config['intents'] as $intentName => $intentDef) {
            $entry = "- {$intentName}: {$intentDef['description']}";
            if (!empty($intentDef['param_fields'])) {
                $entry .= ' Parameters: {' . implode(', ', $intentDef['param_fields']) . '}';
            }
            $intentLines[] = $entry;
        }

        $intentList = implode("\n", $intentLines);
        $paramSchema = self::PARAMETER_SCHEMA;
        $context = $this->contextBuilder->buildContext();

        $contextBlock = '';
        if ($context !== '') {
            $contextBlock = <<<CTX

School Context:
{$context}
CTX;
        }

        return <<<PROMPT
Classify intent for "{$module}" module.
{$intentList}

{$paramSchema}{$contextBlock}

Return JSON: {"intent":"...","parameters":{},"confidence":0.97,"action":"query"}
PROMPT;
    }

    private function buildUnknownModulePrompt(string $module): string
    {
        return <<<PROMPT
You are an intent classifier for the "{$module}" module of a School ERP.

No specific intents defined for this module.

Return JSON only.
{"intent":"unknown","parameters":{},"confidence":0.0,"action":"unknown"}
PROMPT;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    public function getModuleNames(): array
    {
        return array_keys($this->modules);
    }

    public function getModuleIntents(string $module): array
    {
        return $this->modules[$module]['intents'] ?? [];
    }

    public function getModuleDescription(string $module): string
    {
        return $this->modules[$module]['description'] ?? '';
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(mb_strlen($text) / 4);
    }
}
