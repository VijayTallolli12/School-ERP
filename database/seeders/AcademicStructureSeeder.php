<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\ClassSubject;
use App\Modules\Academics\Models\AcademicTerm;
use App\Modules\Academics\Models\SchoolClass;
use App\Modules\Academics\Models\Section;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->where('code', 'DEMO')->firstOrFail();
        $academicYear = AcademicYear::query()->where('school_id', $school->id)->where('is_active', true)->firstOrFail();

        $classes = collect([
            ['name' => 'Class 1', 'code' => 'C1', 'sort_order' => 1],
            ['name' => 'Class 2', 'code' => 'C2', 'sort_order' => 2],
            ['name' => 'Class 3', 'code' => 'C3', 'sort_order' => 3],
            ['name' => 'Class 4', 'code' => 'C4', 'sort_order' => 4],
            ['name' => 'Class 5', 'code' => 'C5', 'sort_order' => 5],
        ])->map(fn (array $class) => SchoolClass::query()->firstOrCreate(
            ['school_id' => $school->id, 'code' => $class['code']],
            ['name' => $class['name'], 'sort_order' => $class['sort_order'], 'status' => 'active'],
        ));

        $sections = collect(['A', 'B'])->map(fn (string $section) => Section::query()->firstOrCreate(
            ['school_id' => $school->id, 'code' => $section],
            ['name' => 'Section '.$section, 'capacity' => 40, 'status' => 'active'],
        ));

        $classes->each(function (SchoolClass $class) use ($school, $sections): void {
            $sections->each(fn (Section $section) => ClassSection::query()->firstOrCreate(
                [
                    'school_id' => $school->id,
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                ],
                ['status' => 'active'],
            ));
        });

        collect([
            ['name' => 'Term 1', 'starts_on' => $academicYear->starts_on, 'ends_on' => $academicYear->starts_on->copy()->addMonths(5)->endOfMonth(), 'sort_order' => 1],
            ['name' => 'Term 2', 'starts_on' => $academicYear->starts_on->copy()->addMonths(6), 'ends_on' => $academicYear->ends_on, 'sort_order' => 2],
        ])->each(fn (array $term) => AcademicTerm::query()->firstOrCreate(
            ['school_id' => $school->id, 'academic_year_id' => $academicYear->id, 'name' => $term['name']],
            ['starts_on' => $term['starts_on'], 'ends_on' => $term['ends_on'], 'sort_order' => $term['sort_order'], 'status' => 'active'],
        ));

        $subjects = collect([
            ['name' => 'English', 'code' => 'ENG', 'type' => 'core', 'credit_hours' => 5],
            ['name' => 'Mathematics', 'code' => 'MATH', 'type' => 'core', 'credit_hours' => 6],
            ['name' => 'Science', 'code' => 'SCI', 'type' => 'core', 'credit_hours' => 5],
            ['name' => 'Social Studies', 'code' => 'SST', 'type' => 'core', 'credit_hours' => 4],
            ['name' => 'Computer Science', 'code' => 'CS', 'type' => 'elective', 'credit_hours' => 3],
        ])->map(fn (array $subject) => Subject::query()->firstOrCreate(
            ['school_id' => $school->id, 'code' => $subject['code']],
            ['name' => $subject['name'], 'type' => $subject['type'], 'credit_hours' => $subject['credit_hours'], 'status' => 'active'],
        ));

        $classes->each(function (SchoolClass $class) use ($school, $academicYear, $subjects): void {
            $subjects->each(fn (Subject $subject) => ClassSubject::query()->firstOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                ],
                ['weekly_periods' => max(1, (int) $subject->credit_hours), 'status' => 'active'],
            ));
        });
    }
}
