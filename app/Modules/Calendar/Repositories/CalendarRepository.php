<?php

namespace App\Modules\Calendar\Repositories;

use App\Modules\Calendar\Models\AcademicCalendar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CalendarRepository implements CalendarRepositoryInterface
{
    public function all(): Collection
    {
        return AcademicCalendar::with(['academicYear', 'creator'])->latest()->get();
    }

    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage);
    }

    public function query(array $filters = []): Builder
    {
        $query = AcademicCalendar::with(['academicYear', 'creator']);

        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (!empty($filters['audience'])) {
            $query->where('audience', $filters['audience']);
        }

        if (isset($filters['is_published']) && $filters['is_published'] !== '') {
            $query->where('is_published', filter_var($filters['is_published'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year'])
                ->whereMonth('start_date', $filters['month']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query;
    }

    public function find(int $id): ?AcademicCalendar
    {
        return AcademicCalendar::with(['academicYear', 'creator', 'updater'])->find($id);
    }

    public function create(array $data): AcademicCalendar
    {
        return AcademicCalendar::create($data);
    }

    public function update(int $id, array $data): AcademicCalendar
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool
    {
        $record = $this->find($id);
        return $record?->delete() ?? false;
    }

    public function getUpcoming(int $limit = 10): Collection
    {
        return AcademicCalendar::published()
            ->upcoming($limit)
            ->with(['academicYear', 'creator'])
            ->get();
    }

    public function getByMonth(int $year, int $month): Collection
    {
        return AcademicCalendar::byMonth($year, $month)
            ->with(['academicYear', 'creator'])
            ->get();
    }

    public function getPublishedByMonth(int $year, int $month): Collection
    {
        return AcademicCalendar::published()
            ->byMonth($year, $month)
            ->with(['academicYear', 'creator'])
            ->get();
    }

    public function togglePublish(int $id): bool
    {
        $record = $this->find($id);
        $record->update(['is_published' => !$record->is_published]);
        return $record->is_published;
    }
}
