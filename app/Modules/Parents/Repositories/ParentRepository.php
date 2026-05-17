<?php

namespace App\Modules\Parents\Repositories;

use App\Modules\Parents\Models\Guardian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ParentRepository implements ParentRepositoryInterface
{
    public function query(): Builder
    {
        return Guardian::query();
    }

    public function findById(int $id): ?Guardian
    {
        return $this->query()->find($id);
    }

    public function findByUuid(string $uuid): ?Guardian
    {
        return $this->query()->where('uuid', $uuid)->first();
    }

    public function getAll(): Collection
    {
        return $this->query()->get();
    }

    public function getActive(): Collection
    {
        return $this->query()->where('status', 'active')->get();
    }

    public function filterQuery(Builder $query, array $filters = []): Builder
    {
        if (!empty($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }

    public function create(array $data): Guardian
    {
        return $this->query()->create($data);
    }

    public function update(Guardian $parent, array $data): Guardian
    {
        $parent->update($data);

        return $parent->fresh();
    }

    public function delete(Guardian $parent): bool
    {
        return $parent->delete();
    }

    public function getWithStudents(int $parentId): Guardian
    {
        return $this->query()
            ->with(['students' => function ($query) {
                $query->with(['sessions' => function ($sessionQuery) {
                    $sessionQuery->with('classSection.schoolClass', 'classSection.section')
                        ->where('status', 'active');
                }]);
            }])
            ->findOrFail($parentId);
    }

    public function getParentsForStudent(int $studentId): Collection
    {
        return $this->query()
            ->whereHas('students', function (Builder $query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->with(['students' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId)
                    ->withPivot('relationship', 'is_primary');
            }])
            ->get();
    }
}