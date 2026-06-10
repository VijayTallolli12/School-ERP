<?php

namespace App\Modules\Calendar\Repositories;

use App\Modules\Calendar\Models\AcademicCalendar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CalendarRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?AcademicCalendar;

    public function create(array $data): AcademicCalendar;

    public function update(int $id, array $data): AcademicCalendar;

    public function delete(int $id): bool;

    public function getUpcoming(int $limit = 10): Collection;

    public function getByMonth(int $year, int $month): Collection;

    public function getPublishedByMonth(int $year, int $month): Collection;

    public function togglePublish(int $id): bool;

    public function query(array $filters = []): Builder;
}
