<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Exams\Models\Exam;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic exam records for the AI regression suite.
 *
 * Includes the mandated scenario: a Half Yearly exam on 2026-01-31 for
 * Class 1 - Section A / Computer Science / Max Marks 100 / Completed.
 *
 * Idempotent — safe to re-run.
 */
class AiExamDataSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->first()
            ?? School::query()->first();

        if (!$school) {
            return;
        }

        $academicYear = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->first()
            ?? AcademicYear::query()->where('school_id', $school->id)->first();

        $classSection = ClassSection::query()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'section'])
            ->get()
            ->first(fn (ClassSection $cs) => $cs->schoolClass?->name === 'Class 1' && $cs->section?->name === 'Section A')
            ?? ClassSection::query()->where('school_id', $school->id)->first();

        $subject = Subject::query()
            ->where('school_id', $school->id)
            ->where('name', 'Computer Science')
            ->first()
            ?? Subject::query()->where('school_id', $school->id)->first();

        if (!$academicYear || !$classSection || !$subject) {
            return;
        }

        // ── Mandated scenario: Half Yearly on 31 Jan 2026 ───────────────
        Exam::query()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSection->id,
                'subject_id' => $subject->id,
                'exam_date' => '2026-01-31',
                'exam_type' => 'half_yearly',
            ],
            [
                'exam_name' => 'Half Yearly Exam',
                'maximum_marks' => 100,
                'pass_marks' => 40,
                'status' => 'completed',
                'is_published' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        // ── Additional January 2026 exams (varied classes/subjects) ─────
        $januaryExams = [
            ['2026-01-15', 'mid_term', 'Class 1', 'Section A', 'English'],
            ['2026-01-20', 'mid_term', 'Class 1', 'Section A', 'Mathematics'],
            ['2026-01-22', 'class_test', 'Class 1', 'Section A', 'Science'],
            ['2026-01-28', 'half_yearly', 'Class 2', 'Section B', 'Mathematics'],
            ['2026-01-30', 'half_yearly', 'Class 3', 'Section A', 'Social Studies'],
        ];

        foreach ($januaryExams as [$date, $type, $className, $sectionName, $subjectName]) {
            $cs = $this->resolveClassSection($school->id, $className, $sectionName);
            $subj = $this->resolveSubject($school->id, $subjectName);

            if (!$cs || !$subj) {
                continue;
            }

            Exam::query()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'class_section_id' => $cs->id,
                    'subject_id' => $subj->id,
                    'exam_date' => $date,
                    'exam_type' => $type,
                ],
                [
                    'exam_name' => ucwords(str_replace('_', ' ', $type)) . ' Exam',
                    'maximum_marks' => 100,
                    'pass_marks' => 40,
                    'status' => $date < '2026-02-01' ? 'completed' : 'scheduled',
                    'is_published' => true,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]
            );
        }

        // ── A scheduled (upcoming) exam for "upcoming exams" queries ─────
        Exam::query()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'class_section_id' => $classSection->id,
                'subject_id' => $subject->id,
                'exam_date' => '2026-09-15',
                'exam_type' => 'quarterly',
            ],
            [
                'exam_name' => 'Quarterly Exam',
                'maximum_marks' => 100,
                'pass_marks' => 40,
                'status' => 'scheduled',
                'is_published' => false,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );
    }

    private function resolveClassSection(int $schoolId, string $className, string $sectionName): ?ClassSection
    {
        return ClassSection::query()
            ->where('school_id', $schoolId)
            ->whereHas('schoolClass', fn ($q) => $q->where('name', $className))
            ->whereHas('section', fn ($q) => $q->where('name', $sectionName))
            ->first();
    }

    private function resolveSubject(int $schoolId, string $subjectName): ?Subject
    {
        return Subject::query()
            ->where('school_id', $schoolId)
            ->where('name', $subjectName)
            ->first();
    }
}
