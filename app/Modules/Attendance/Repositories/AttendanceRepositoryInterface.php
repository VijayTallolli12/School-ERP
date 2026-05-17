<?php

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

interface AttendanceRepositoryInterface
{
    public function query(): Builder;

    public function getForDataTable(array $filters = []): Paginator;

    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): Attendance;

    public function delete(Attendance $attendance): void;

    public function findByDateAndStudent(string $date, int $studentId): ?Attendance;

    public function getByClassSectionAndDate(int $classSectionId, string $date): Builder;

    public function getMonthlyReport(int $classSectionId, int $month, int $year): array;

    public function filterQuery(Builder $query, array $filters): Builder;

    /**
     * @return array{totals: array<string, int>, total_records: int, attendance_rate: float, by_class: array<int, array{class_section_id: int, label: string, total: int, present: int, absent: int, late: int, half_day: int, excused: int, rate: float}>}
     */
    public function getStatistics(array $filters = []): array;

    /**
     * @return list<array{student_id: int, name: string, roll_no: string|null, counts: array<string, int>, total_marked: int}>
     */
    public function getMonthlyStudentBreakdown(int $classSectionId, int $month, int $year): array;
}
