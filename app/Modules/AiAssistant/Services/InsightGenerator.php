<?php

namespace App\Modules\AiAssistant\Services;

use Illuminate\Support\Facades\Log;

class InsightGenerator
{
    private const HEALTH_THRESHOLDS = [
        'attendance' => [
            'excellent' => 90,
            'good' => 75,
            'warning' => 60,
        ],
        'fee_collection' => [
            'excellent' => 80,
            'good' => 60,
            'warning' => 40,
        ],
        'transport_utilization' => [
            'excellent' => 85,
            'good' => 70,
            'warning' => 50,
        ],
    ];

    public function generate(array $orchestrationOutput): array
    {
        $sections = $orchestrationOutput['sections'] ?? [];

        $kpis = $this->extractKPIs($sections);
        $healthScore = $this->calculateHealthScore($kpis);
        $anomalies = $this->detectAnomalies($sections, $kpis);
        $alerts = $this->generateAlerts($kpis, $anomalies);
        $recommendations = $this->generateRecommendations($kpis, $anomalies);

        return [
            'health_score' => $healthScore,
            'kpis' => $kpis,
            'anomalies' => $anomalies,
            'alerts' => $alerts,
            'recommendations' => $recommendations,
            'operational_status' => $this->getOperationalStatus($sections),
        ];
    }

    private function extractKPIs(array $sections): array
    {
        $kpis = [];

        if (isset($sections['attendance']['data'])) {
            $attendance = $this->parseAttendanceData($sections['attendance']['data']);
            if ($attendance) {
                $kpis['attendance'] = $attendance;
            }
        }

        if (isset($sections['fees']['data'])) {
            $fees = $this->parseFeeData($sections['fees']['data']);
            if ($fees) {
                $kpis['fees'] = $fees;
            }
        }

        if (isset($sections['transport']['data'])) {
            $transport = $this->parseTransportData($sections['transport']['data']);
            if ($transport) {
                $kpis['transport'] = $transport;
            }
        }

        if (isset($sections['library']['data'])) {
            $library = $this->parseLibraryData($sections['library']['data']);
            if ($library) {
                $kpis['library'] = $library;
            }
        }

        if (isset($sections['homework']['data'])) {
            $homework = $this->parseHomeworkData($sections['homework']['data']);
            if ($homework) {
                $kpis['homework'] = $homework;
            }
        }

        if (isset($sections['exams']['data'])) {
            $exams = $this->parseExamData($sections['exams']['data']);
            if ($exams) {
                $kpis['exams'] = $exams;
            }
        }

        if (isset($sections['leave']['data'])) {
            $leave = $this->parseLeaveData($sections['leave']['data']);
            if ($leave) {
                $kpis['leave'] = $leave;
            }
        }

        return $kpis;
    }

    private function parseAttendanceData(string $data): ?array
    {
        if (preg_match('/(\d+)\s*(?:out of|\/)\s*(\d+)\s*(?:marked|students)/i', $data, $m)) {
            $total = (int) $m[2];
            $present = (int) $m[1];
            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            return [
                'total' => $total,
                'present' => $present,
                'percentage' => $percentage,
            ];
        }

        if (preg_match('/(\d+\.?\d*)%/', $data, $m)) {
            return [
                'percentage' => (float) $m[1],
            ];
        }

        return null;
    }

    private function parseFeeData(string $data): ?array
    {
        $result = [];

        if (preg_match('/₹[\s]*([\d,]+\.?\d*)/', $data, $m)) {
            $result['amount'] = (float) str_replace(',', '', $m[1]);
        }

        if (preg_match('/(\d+\.?\d*)%/', $data, $m)) {
            $result['rate'] = (float) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function parseTransportData(string $data): ?array
    {
        $result = [];

        if (preg_match('/(\d+)\s*route/i', $data, $m)) {
            $result['routes'] = (int) $m[1];
        }

        if (preg_match('/(\d+\.?\d*)%\s*(?:utilization|occupancy|capacity)/i', $data, $m)) {
            $result['utilization'] = (float) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function parseLibraryData(string $data): ?array
    {
        $result = [];

        if (preg_match('/(\d+)\s*(?:book|issued)/i', $data, $m)) {
            $result['issued'] = (int) $m[1];
        }

        if (preg_match('/(\d+)\s*overdue/i', $data, $m)) {
            $result['overdue'] = (int) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function parseHomeworkData(string $data): ?array
    {
        $result = [];

        if (preg_match('/(\d+)\s*(?:assigned|homework)/i', $data, $m)) {
            $result['assigned'] = (int) $m[1];
        }

        if (preg_match('/(\d+)\s*(?:due|overdue)/i', $data, $m)) {
            $result['overdue'] = (int) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function parseExamData(string $data): ?array
    {
        $result = [];

        if (preg_match('/(\d+)\s*published/i', $data, $m)) {
            $result['published'] = (int) $m[1];
        }

        if (preg_match('/(\d+)\s*(?:unpublished|pending)/i', $data, $m)) {
            $result['pending'] = (int) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function parseLeaveData(string $data): ?array
    {
        $result = [];

        if (preg_match('/(\d+)\s*(?:pending|request)/i', $data, $m)) {
            $result['pending'] = (int) $m[1];
        }

        if (preg_match('/(\d+)\s*approved/i', $data, $m)) {
            $result['approved'] = (int) $m[1];
        }

        return !empty($result) ? $result : null;
    }

    private function calculateHealthScore(array $kpis): array
    {
        $scores = [];

        if (isset($kpis['attendance']['percentage'])) {
            $pct = $kpis['attendance']['percentage'];
            $scores['attendance'] = $this->scoreFromThresholds($pct, self::HEALTH_THRESHOLDS['attendance']);
        }

        if (isset($kpis['fees']['rate'])) {
            $rate = $kpis['fees']['rate'];
            $scores['fees'] = $this->scoreFromThresholds($rate, self::HEALTH_THRESHOLDS['fee_collection']);
        }

        if (isset($kpis['transport']['utilization'])) {
            $util = $kpis['transport']['utilization'];
            $scores['transport'] = $this->scoreFromThresholds($util, self::HEALTH_THRESHOLDS['transport_utilization']);
        }

        if (isset($kpis['library']['overdue']) && $kpis['library']['overdue'] > 10) {
            $scores['library'] = 60;
        } elseif (isset($kpis['library'])) {
            $scores['library'] = 85;
        }

        if (isset($kpis['homework']['overdue']) && $kpis['homework']['overdue'] > 5) {
            $scores['homework'] = 65;
        } elseif (isset($kpis['homework'])) {
            $scores['homework'] = 85;
        }

        if (empty($scores)) {
            return ['overall' => 0, 'rating' => 'insufficient_data'];
        }

        $overall = (int) round(array_sum($scores) / count($scores));

        return [
            'overall' => $overall,
            'rating' => $this->getRating($overall),
            'breakdown' => $scores,
        ];
    }

    private function scoreFromThresholds(float $value, array $thresholds): int
    {
        if ($value >= $thresholds['excellent']) {
            return 95;
        }
        if ($value >= $thresholds['good']) {
            return 80;
        }
        if ($value >= $thresholds['warning']) {
            return 60;
        }
        return 40;
    }

    private function getRating(int $score): string
    {
        if ($score >= 90) {
            return 'excellent';
        }
        if ($score >= 75) {
            return 'good';
        }
        if ($score >= 60) {
            return 'warning';
        }
        return 'critical';
    }

    private function detectAnomalies(array $sections, array $kpis): array
    {
        $anomalies = [];

        if (isset($kpis['attendance']['percentage']) && $kpis['attendance']['percentage'] < 60) {
            $anomalies[] = [
                'type' => 'warning',
                'module' => 'attendance',
                'message' => 'Attendance is critically low',
                'value' => $kpis['attendance']['percentage'],
            ];
        }

        if (isset($kpis['library']['overdue']) && $kpis['library']['overdue'] > 20) {
            $anomalies[] = [
                'type' => 'warning',
                'module' => 'library',
                'message' => 'High number of overdue books',
                'value' => $kpis['library']['overdue'],
            ];
        }

        if (isset($kpis['homework']['overdue']) && $kpis['homework']['overdue'] > 10) {
            $anomalies[] = [
                'type' => 'warning',
                'module' => 'homework',
                'message' => 'Many homework assignments overdue',
                'value' => $kpis['homework']['overdue'],
            ];
        }

        foreach ($sections as $module => $section) {
            if (isset($section['status']) && $section['status'] === 'unavailable') {
                $anomalies[] = [
                    'type' => 'error',
                    'module' => $module,
                    'message' => ucfirst($module) . ' data unavailable',
                    'value' => null,
                ];
            }
        }

        return $anomalies;
    }

    private function generateAlerts(array $kpis, array $anomalies): array
    {
        $alerts = [];

        foreach ($anomalies as $anomaly) {
            if ($anomaly['type'] === 'error') {
                $alerts[] = [
                    'severity' => 'high',
                    'message' => $anomaly['message'],
                    'module' => $anomaly['module'],
                ];
            }
        }

        if (isset($kpis['attendance']['percentage']) && $kpis['attendance']['percentage'] < 75) {
            $alerts[] = [
                'severity' => 'medium',
                'message' => 'Attendance below 75% threshold',
                'module' => 'attendance',
            ];
        }

        if (isset($kpis['fees']['rate']) && $kpis['fees']['rate'] < 50) {
            $alerts[] = [
                'severity' => 'medium',
                'message' => 'Fee collection rate below 50%',
                'module' => 'fees',
            ];
        }

        return $alerts;
    }

    private function generateRecommendations(array $kpis, array $anomalies): array
    {
        $recommendations = [];

        if (isset($kpis['attendance']['percentage']) && $kpis['attendance']['percentage'] < 75) {
            $recommendations[] = [
                'priority' => 'high',
                'module' => 'attendance',
                'action' => 'Review attendance policies and send parent notifications',
            ];
        }

        if (isset($kpis['fees']['rate']) && $kpis['fees']['rate'] < 60) {
            $recommendations[] = [
                'priority' => 'high',
                'module' => 'fees',
                'action' => 'Send fee reminders and follow up with defaulters',
            ];
        }

        if (isset($kpis['library']['overdue']) && $kpis['library']['overdue'] > 10) {
            $recommendations[] = [
                'priority' => 'medium',
                'module' => 'library',
                'action' => 'Send overdue book reminders to students and parents',
            ];
        }

        if (isset($kpis['homework']['overdue']) && $kpis['homework']['overdue'] > 5) {
            $recommendations[] = [
                'priority' => 'medium',
                'module' => 'homework',
                'action' => 'Review homework submission policies',
            ];
        }

        return $recommendations;
    }

    private function getOperationalStatus(array $sections): array
    {
        $status = [];

        foreach ($sections as $module => $section) {
            $status[$module] = [
                'label' => $section['label'] ?? ucfirst($module),
                'status' => $section['status'] ?? 'unknown',
            ];
        }

        return $status;
    }
}
