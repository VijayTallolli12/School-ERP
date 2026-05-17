<?php

namespace App\Modules\Parents\Repositories;

use App\Modules\Parents\Models\Guardian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ParentRepositoryInterface
{
    public function query(): Builder;

    public function findById(int $id): ?Guardian;

    public function findByUuid(string $uuid): ?Guardian;

    public function getAll(): Collection;

    public function getActive(): Collection;

    public function filterQuery(Builder $query, array $filters = []): Builder;

    public function create(array $data): Guardian;

    public function update(Guardian $parent, array $data): Guardian;

    public function delete(Guardian $parent): bool;

    public function getWithStudents(int $parentId): Guardian;

    public function getParentsForStudent(int $studentId): Collection;
}