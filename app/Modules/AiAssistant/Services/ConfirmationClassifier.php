<?php

namespace App\Modules\AiAssistant\Services;

/**
 * Classifies a follow-up message into one of:
 *
 *   - confirm      (the user agrees to execute the pending action)
 *   - cancel       (the user declines / aborts)
 *   - ambiguous    (the user is unsure — do NOT execute, ask again)
 *   - other        (a new/modified request — supersede the pending action)
 *
 * Uses normalized, tolerant matching (case/whitespace/punctuation-insensitive)
 * over reusable phrase lists — never a hardcoded `if ($message === 'Sure')`.
 */
class ConfirmationClassifier
{
    public const CONFIRM = 'confirm';
    public const CANCEL = 'cancel';
    public const AMBIGUOUS = 'ambiguous';
    public const OTHER = 'other';

    private const CONFIRM_PHRASES = [
        'yes', 'yes please', 'yes send it', 'yes send', 'yes do it', 'yes proceed',
        'sure', 'sure send it', 'sure go ahead', 'sure please', 'sure do it',
        'go ahead', 'send it', 'send', 'confirm', 'confirm it', 'confirm this',
        'proceed', 'do it', 'please do', 'please proceed', 'okay', 'ok', 'ok do it',
        'ok send it', 'yep', 'yeah', 'yup', 'yes, please', 'yes, send it',
        'please send it', 'go ahead and send', 'absolutely', 'do it please',
    ];

    private const CANCEL_PHRASES = [
        'no', 'nope', 'nah', 'no thanks', 'no thank you', 'don\'t', 'dont',
        'don\'t send it', 'dont send it', 'don\'t send', 'dont send',
        'don\'t do it', 'dont do it', 'cancel', 'cancel it', 'cancel this',
        'cancel that', 'stop', 'stop it', 'not now', 'never mind', 'nevermind',
        'forget it', 'forget about it', 'abort', 'abort it', 'leave it',
        'skip', 'skip it', 'no, don\'t', 'no dont', 'no, cancel', 'no cancel',
    ];

    private const AMBIGUOUS_PHRASES = [
        'maybe', 'perhaps', 'i am not sure', 'i\'m not sure', 'im not sure',
        'not sure', 'not certain', 'i don\'t know', 'i dont know', 'i do not know',
        'do you think so', 'what do you think', 'i\'m unsure', 'im unsure',
        'i am unsure', 'let me think', 'hold on', 'hmm', 'wait', 'is it safe',
        'are you sure', 'should i', 'will it work',
    ];

    private const MODIFICATION_MARKERS = [
        'actually', 'instead', 'only', 'just', 'change', 'changed', 'modify',
        'rather', 'but', 'not that', 'other', 'different', 'revise', 'instead of',
    ];

    public function classify(string $message): string
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return self::OTHER;
        }

        // 1. Exact phrase match (highest confidence).
        if (in_array($normalized, self::CONFIRM_PHRASES, true)) {
            return self::CONFIRM;
        }
        if (in_array($normalized, self::CANCEL_PHRASES, true)) {
            return self::CANCEL;
        }
        if (in_array($normalized, self::AMBIGUOUS_PHRASES, true)) {
            return self::AMBIGUOUS;
        }

        // 2. A modification/new request ("Actually, send it only to Class 5",
        //    "only to parents", "instead to Class 3") is NEVER a plain
        //    confirmation — the pending action must be superseded, not fired.
        foreach (self::MODIFICATION_MARKERS as $marker) {
            if (str_contains($normalized, $marker)) {
                return self::OTHER;
            }
        }

        // 3. Cancellation / ambiguous containment is safe (never executes).
        foreach (self::CANCEL_PHRASES as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return self::CANCEL;
            }
        }

        foreach (self::AMBIGUOUS_PHRASES as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return self::AMBIGUOUS;
            }
        }

        // 4. Confirm by short-prefix: a short message that STARTS with an
        //    agreement word ("Yes, do it", "Ok go ahead", "Sure send it").
        //    Long messages or ones with extra instructions fall to OTHER.
        $words = str_word_count($normalized, 1);
        if (count($words) <= 4) {
            $head = implode(' ', array_slice($words, 0, 2));
            if (preg_match('/^(yes|sure|ok|okay|yep|yeah|yup|go ahead|proceed|do it|send it|confirm|absolutely)\b/', $head)) {
                return self::CONFIRM;
            }
        }

        return self::OTHER;
    }

    public function isConfirmation(string $message): bool
    {
        return $this->classify($message) === self::CONFIRM;
    }

    public function isCancellation(string $message): bool
    {
        return $this->classify($message) === self::CANCEL;
    }

    public function isAmbiguous(string $message): bool
    {
        return $this->classify($message) === self::AMBIGUOUS;
    }

    /**
     * Normalize: lowercase, strip punctuation/extra whitespace, trim.
     */
    private function normalize(string $message): string
    {
        $text = mb_strtolower(trim($message));
        $text = preg_replace('/[^a-z0-9\s\']/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
