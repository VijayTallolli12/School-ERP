<?php

namespace App\Modules\AiAssistant\Erp;

use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/**
 * Parses natural-language date/time expressions into concrete date ranges.
 *
 * Supports: today, yesterday, tomorrow, this week, last week, next week,
 * this month, last month, next month, this academic year, last academic year,
 * "January 2026", "Jan 2026", "15 January 2026", "between Jan and March 2026",
 * "first week of January", "early January", etc.
 */
class NaturalLanguageDateParser
{
    private const MONTHS = [
        'january' => 1, 'jan' => 1,
        'february' => 2, 'feb' => 2,
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12,
    ];

    private const DAY_NAMES = [
        'monday' => 1, 'mon' => 1,
        'tuesday' => 2, 'tue' => 2, 'tues' => 2,
        'wednesday' => 3, 'wed' => 3,
        'thursday' => 4, 'thu' => 4, 'thur' => 4, 'thurs' => 4,
        'friday' => 5, 'fri' => 5,
        'saturday' => 6, 'sat' => 6,
        'sunday' => 0, 'sun' => 0,
    ];

    public function parse(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $lower = mb_strtolower(trim($text));
        $lower = preg_replace('/\s+/', ' ', $lower) ?? $lower;

        if ($range = $this->parseRelative($lower)) {
            return $range;
        }

        if ($range = $this->parseExplicitRange($lower)) {
            return $range;
        }

        if ($range = $this->parseFullDate($lower)) {
            return $range;
        }

        if ($range = $this->parseMonthYear($lower)) {
            return $range;
        }

        if ($range = $this->parseYearOnly($lower)) {
            return $range;
        }

        if ($range = $this->parseMonthNameOnly($lower)) {
            return $range;
        }

        return null;
    }

    /**
     * today / yesterday / tomorrow / this week / last month / academic years
     */
    private function parseRelative(string $lower): ?array
    {
        $now = Carbon::now();

        if (str_contains($lower, 'today') || $lower === 'today' || str_contains($lower, 'current day')) {
            return $this->singleDay($now->format('Y-m-d'));
        }

        if (str_contains($lower, 'yesterday')) {
            return $this->singleDay($now->subDay()->format('Y-m-d'));
        }

        if (str_contains($lower, 'tomorrow')) {
            return $this->singleDay($now->addDay()->format('Y-m-d'));
        }

        if (preg_match('/\bthis week\b|\bcurrent week\b/', $lower)) {
            return $this->singleDayRange($now->startOfWeek()->format('Y-m-d'), $now->copy()->endOfWeek()->format('Y-m-d'));
        }

        if (preg_match('/\blast week\b|\bprevious week\b/', $lower) && !preg_match('/\b(?:last|previous)\s+week\s+of\s+[a-z]+\b/', $lower)) {
            $start = $now->copy()->startOfWeek()->subWeek();
            return $this->singleDayRange($start->format('Y-m-d'), $start->copy()->endOfWeek()->format('Y-m-d'));
        }

        if (preg_match('/\bnext week\b/', $lower) && !preg_match('/\bnext\s+week\s+of\s+[a-z]+\b/', $lower)) {
            $start = $now->copy()->startOfWeek()->addWeek();
            return $this->singleDayRange($start->format('Y-m-d'), $start->copy()->endOfWeek()->format('Y-m-d'));
        }

        if (preg_match('/\bthis month\b|\bcurrent month\b|\bmonthly\b/', $lower)) {
            return $this->singleDayRange($now->copy()->startOfMonth()->format('Y-m-d'), $now->copy()->endOfMonth()->format('Y-m-d'));
        }

        if (preg_match('/\blast month\b|\bprevious month\b/', $lower)) {
            $start = $now->copy()->startOfMonth()->subMonth();
            return $this->singleDayRange($start->format('Y-m-d'), $start->copy()->endOfMonth()->format('Y-m-d'));
        }

        if (preg_match('/\bnext month\b/', $lower)) {
            $start = $now->copy()->startOfMonth()->addMonth();
            return $this->singleDayRange($start->format('Y-m-d'), $start->copy()->endOfMonth()->format('Y-m-d'));
        }

        if (preg_match('/\bthis year\b|\bcurrent year\b/', $lower)) {
            return $this->singleDayRange($now->copy()->startOfYear()->format('Y-m-d'), $now->copy()->endOfYear()->format('Y-m-d'));
        }

        if (preg_match('/\blast year\b|\bprevious year\b/', $lower)) {
            $start = $now->copy()->startOfYear()->subYear();
            return $this->singleDayRange($start->format('Y-m-d'), $start->copy()->endOfYear()->format('Y-m-d'));
        }

        if (preg_match('/\bthis academic year\b|\bcurrent academic year\b/', $lower)) {
            return $this->academicYearRange('current');
        }

        if (preg_match('/\blast academic year\b|\bprevious academic year\b/', $lower)) {
            return $this->academicYearRange('previous');
        }

        if (preg_match('/\bnext academic year\b/', $lower)) {
            return $this->academicYearRange('next');
        }

        // "first week of january 2026" / "last week of january"
        if (preg_match('/(first|1st|second|2nd|third|3rd|fourth|4th|last)\s+week\s+of\s+([a-z]+)(?:\s+(\d{4}))?/', $lower, $m)) {
            $month = self::MONTHS[$m[2]] ?? null;
            $year = isset($m[3]) ? (int) $m[3] : $now->year;
            if ($month) {
                $monthStart = Carbon::create($year, $month, 1);
                $daysInMonth = $monthStart->copy()->endOfMonth()->day;

                if (in_array($m[1], ['last'], true)) {
                    $start = $monthStart->copy()->endOfMonth()->subDays(6)->startOfDay();
                    $end = $monthStart->copy()->endOfMonth();
                } else {
                    $dayStart = match ($m[1]) {
                        'first', '1st' => 1,
                        'second', '2nd' => 8,
                        'third', '3rd' => 15,
                        'fourth', '4th' => 22,
                        default => 1,
                    };
                    $start = Carbon::create($year, $month, $dayStart);
                    $end = Carbon::create($year, $month, min($dayStart + 6, $daysInMonth));
                }

                return $this->singleDayRange($start->format('Y-m-d'), $end->format('Y-m-d'));
            }
        }

        // "early january" / "mid january" / "end of january"
        if (preg_match('/\b(early|mid|middle|end)\s+(?:of\s+)?([a-z]+)(?:\s+(\d{4}))?/', $lower, $m)) {
            $month = self::MONTHS[$m[2]] ?? null;
            $year = isset($m[3]) ? (int) $m[3] : $now->year;
            if ($month) {
                $monthStart = Carbon::create($year, $month, 1);
                $monthEnd = $monthStart->copy()->endOfMonth();

                $start = match ($m[1]) {
                    'early' => $monthStart->copy()->startOfMonth(),
                    'mid', 'middle' => $monthStart->copy()->startOfMonth()->addDays(10),
                    default => $monthStart->copy()->startOfMonth()->addDays(20),
                };
                $end = match ($m[1]) {
                    'early' => $monthStart->copy()->startOfMonth()->addDays(9)->endOfDay(),
                    'mid', 'middle' => $monthStart->copy()->startOfMonth()->addDays(19)->endOfDay(),
                    default => $monthEnd->copy()->endOfDay(),
                };

                return $this->singleDayRange($start->format('Y-m-d'), $end->format('Y-m-d'));
            }
        }

        return null;
    }

    /**
     * "between January 2026 and March 2026", "from Jan 1 to Jan 15 2026",
     * "1-15 January 2026", "January 1 - March 31 2026"
     */
    private function parseExplicitRange(string $lower): ?array
    {
        // between X and Y (Y = month/year, or full date)
        if (preg_match('/\bbetween\s+(.+?)\s+and\s+(.+?)(?:\.|$)/', $lower, $m)) {
            $from = $this->parseSingleDateExpression($m[1]);
            $to = $this->parseSingleDateExpression($m[2], endOfPeriod: true);
            if ($from && $to) {
                return $this->singleDayRange($from, $to);
            }
        }

        // from X to Y
        if (preg_match('/\bfrom\s+(.+?)\s+(?:to|until|till|-)\s+(.+?)(?:\.|$)/', $lower, $m)) {
            $from = $this->parseSingleDateExpression($m[1]);
            $to = $this->parseSingleDateExpression($m[2], endOfPeriod: true);
            if ($from && $to) {
                return $this->singleDayRange($from, $to);
            }
        }

        // "January - March 2026" (month range within a year)
        if (preg_match('/([a-z]+)\s*(?:-|to)\s*([a-z]+)\s+(\d{4})/', $lower, $m)) {
            $fromMonth = self::MONTHS[$m[1]] ?? null;
            $toMonth = self::MONTHS[$m[2]] ?? null;
            if ($fromMonth && $toMonth) {
                $year = (int) $m[3];
                $from = Carbon::create($year, $fromMonth, 1);
                $to = Carbon::create($year, $toMonth, 1)->endOfMonth();
                return $this->singleDayRange($from->format('Y-m-d'), $to->format('Y-m-d'));
            }
        }

        return null;
    }

    /**
     * "January 2026", "Jan 2026", "01/2026", "1 2026"
     */
    private function parseMonthYear(string $lower): ?array
    {
        if (preg_match('/([a-z]+)\.?\s+(\d{4})/', $lower, $m)) {
            $month = self::MONTHS[$m[1]] ?? null;
            if ($month) {
                $year = (int) $m[2];
                return $this->singleDayRange(
                    Carbon::create($year, $month, 1)->format('Y-m-d'),
                    Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d')
                );
            }
        }

        if (preg_match('/(\d{1,2})\/(\d{4})/', $lower, $m)) {
            $month = (int) $m[1];
            if ($month >= 1 && $month <= 12) {
                $year = (int) $m[2];
                return $this->singleDayRange(
                    Carbon::create($year, $month, 1)->format('Y-m-d'),
                    Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d')
                );
            }
        }

        return null;
    }

    /**
     * "15 January 2026", "15 jan 2026", "2026-01-15", "January 15, 2026"
     */
    private function parseFullDate(string $lower): ?array
    {
        if (preg_match('/\b(\d{1,2})(?:st|nd|rd|th)?\s+(?:of\s+)?([a-z]+)\.?\s+(\d{4})\b/', $lower, $m)) {
            $day = (int) $m[1];
            $month = self::MONTHS[$m[2]] ?? null;
            if ($month && $day >= 1 && $day <= 31) {
                $date = Carbon::create((int) $m[3], $month, $day);
                if ($date) {
                    return $this->singleDay($date->format('Y-m-d'));
                }
            }
        }

        if (preg_match('/\b([a-z]+)\s+(\d{1,2})(?:st|nd|rd|th)?,?\s+(\d{4})\b/', $lower, $m)) {
            $month = self::MONTHS[$m[1]] ?? null;
            $day = (int) $m[2];
            if ($month && $day >= 1 && $day <= 31) {
                $date = Carbon::create((int) $m[3], $month, $day);
                if ($date) {
                    return $this->singleDay($date->format('Y-m-d'));
                }
            }
        }

        // "January 31" / "Jan 31" (month + day, no year) -> current year.
        if (preg_match('/\b([a-z]+)\s+(\d{1,2})(?:st|nd|rd|th)?\b/', $lower, $m)) {
            $month = self::MONTHS[$m[1]] ?? null;
            $day = (int) $m[2];
            if ($month && $day >= 1 && $day <= 31) {
                $date = Carbon::create(Carbon::now()->year, $month, $day);
                if ($date) {
                    return $this->singleDay($date->format('Y-m-d'));
                }
            }
        }

        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $lower, $m)) {
            $date = Carbon::create((int) $m[1], (int) $m[2], (int) $m[3]);
            if ($date) {
                return $this->singleDay($date->format('Y-m-d'));
            }
        }

        if (preg_match('/\b(\d{1,2})-(\d{1,2})-(\d{2,4})\b/', $lower, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }
            $date = Carbon::create($year, $month, $day);
            if ($date) {
                return $this->singleDay($date->format('Y-m-d'));
            }
        }

        return null;
    }

    /**
     * "1999", "in 1999", "during 2026" — a bare year (no month).
     */
    private function parseYearOnly(string $lower): ?array
    {
        if (preg_match('/\b(?:in|during|of|for)?\s*(\d{4})\b/', $lower, $m)) {
            // Avoid matching a year that is part of a month/year or full date
            // (those are already handled upstream) or a two-digit year suffix.
            $year = (int) $m[1];
            if ($year >= 1900 && $year <= 2100) {
                return $this->singleDayRange(
                    Carbon::create($year, 1, 1)->format('Y-m-d'),
                    Carbon::create($year, 12, 31)->format('Y-m-d')
                );
            }
        }

        return null;
    }

    /**
     * "January", "Jan", "in January", "January 2026" (without explicit year uses current year)
     */
    private function parseMonthNameOnly(string $lower): ?array
    {
        // Skip if a relative month phrase was already handled upstream.
        if (preg_match('/\b(last|next|this|previous|current)\s+month\b/', $lower)) {
            return null;
        }

        $found = null;
        $foundLength = 0;

        // Scan for the longest standalone month name.
        foreach (self::MONTHS as $name => $monthNum) {
            if (preg_match('/\b' . preg_quote($name, '/') . '\b/', $lower)) {
                if (mb_strlen($name) > $foundLength) {
                    $found = $monthNum;
                    $foundLength = mb_strlen($name);
                }
            }
        }

        if ($found === null) {
            return null;
        }

        $year = Carbon::now()->year;

        return $this->singleDayRange(
            Carbon::create($year, $found, 1)->format('Y-m-d'),
            Carbon::create($year, $found, 1)->endOfMonth()->format('Y-m-d')
        );
    }

    private function parseSingleDateExpression(string $expression, bool $endOfPeriod = false): ?string
    {
        $lower = mb_strtolower(trim($expression));

        if ($lower === 'today' || str_contains($lower, 'today')) {
            return Carbon::now()->format('Y-m-d');
        }
        if ($lower === 'yesterday' || str_contains($lower, 'yesterday')) {
            return Carbon::now()->subDay()->format('Y-m-d');
        }
        if ($lower === 'tomorrow' || str_contains($lower, 'tomorrow')) {
            return Carbon::now()->addDay()->format('Y-m-d');
        }

        if (preg_match('/([a-z]+)\.?\s+(\d{4})/', $lower, $m)) {
            $month = self::MONTHS[$m[1]] ?? null;
            if ($month) {
                $date = Carbon::create((int) $m[2], $month, 1);
                if ($endOfPeriod) {
                    $date = $date->copy()->endOfMonth();
                }
                return $date->format('Y-m-d');
            }
        }

        // Bare month name, e.g. "january" (use current year).
        if (preg_match('/\b([a-z]{3,9})\b/', $lower, $m)) {
            $month = self::MONTHS[$m[1]] ?? null;
            if ($month) {
                $date = Carbon::create(Carbon::now()->year, $month, 1);
                if ($endOfPeriod) {
                    $date = $date->copy()->endOfMonth();
                }
                return $date->format('Y-m-d');
            }
        }

        if (preg_match('/\b(\d{1,2})(?:st|nd|rd|th)?\s+(?:of\s+)?([a-z]+)\.?\s+(\d{4})\b/', $lower, $m)) {
            $month = self::MONTHS[$m[2]] ?? null;
            if ($month) {
                return Carbon::create((int) $m[3], $month, (int) $m[1])->format('Y-m-d');
            }
        }

        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $lower, $m)) {
            return Carbon::create((int) $m[1], (int) $m[2], (int) $m[3])->format('Y-m-d');
        }

        return null;
    }

    private function academicYearRange(string $position): ?array
    {
        $schoolId = app(\App\Core\Tenant\SchoolContext::class)->id();

        $query = \App\Models\AcademicYear::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $current = $query
            ->when($position === 'current', fn ($q) => $q->where('is_active', true))
            ->orderBy('starts_on')
            ->first();

        $years = \App\Models\AcademicYear::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->orderBy('starts_on')
            ->get();

        $target = null;
        if ($position === 'current') {
            $target = $current ?? $years->last();
        } elseif ($position === 'previous') {
            $target = $years->filter(fn ($y) => $y->is_active && $y->id !== $current?->id)->last()
                ?? $years->first();
        } else {
            $target = $current ? $years->firstWhere('starts_on', '>', $current->starts_on) : null;
        }

        if (!$target || !$target->starts_on || !$target->ends_on) {
            return null;
        }

        return $this->singleDayRange(
            $target->starts_on->format('Y-m-d'),
            $target->ends_on->format('Y-m-d')
        );
    }

    private function singleDay(string $date): array
    {
        return [
            'date_from' => $date,
            'date_to' => $date,
            'type' => 'single_day',
        ];
    }

    private function singleDayRange(string $from, string $to): array
    {
        return [
            'date_from' => $from,
            'date_to' => $to,
            'type' => 'range',
        ];
    }
}
