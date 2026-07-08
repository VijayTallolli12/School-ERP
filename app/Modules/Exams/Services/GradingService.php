<?php

namespace App\Modules\Exams\Services;

use App\Modules\Exams\Models\GradeScale;
use Illuminate\Support\Facades\Cache;

class GradingService
{
    public function calculateGrade(float $percentage, int $schoolId): array
    {
        $scales = Cache::remember("grade_scales.{$schoolId}", 3600, function () use ($schoolId): array {
            return GradeScale::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderByDesc('min_percentage')
                ->get()
                ->toArray();
        });

        if (! empty($scales)) {
            foreach ($scales as $scale) {
                if ($percentage >= $scale['min_percentage'] && $percentage <= $scale['max_percentage']) {
                    return [
                        'grade' => $scale['grade'],
                        'grade_point' => $scale['grade_point'] !== null ? (float) $scale['grade_point'] : null,
                        'is_fail' => (bool) $scale['is_fail'],
                    ];
                }
            }
        }

        return $this->defaultGrade($percentage);
    }

    private function defaultGrade(float $percentage): array
    {
        return match (true) {
            $percentage >= 90 => ['grade' => 'A+', 'grade_point' => 9.0, 'is_fail' => false],
            $percentage >= 80 => ['grade' => 'A', 'grade_point' => 8.0, 'is_fail' => false],
            $percentage >= 70 => ['grade' => 'B+', 'grade_point' => 7.0, 'is_fail' => false],
            $percentage >= 60 => ['grade' => 'B', 'grade_point' => 6.0, 'is_fail' => false],
            $percentage >= 50 => ['grade' => 'C+', 'grade_point' => 5.0, 'is_fail' => false],
            $percentage >= 40 => ['grade' => 'C', 'grade_point' => 4.0, 'is_fail' => false],
            $percentage >= 33 => ['grade' => 'D', 'grade_point' => 3.0, 'is_fail' => true],
            default => ['grade' => 'F', 'grade_point' => 0.0, 'is_fail' => true],
        };
    }
}
