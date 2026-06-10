<?php

namespace App\Modules\Documents\Repositories;

use App\Modules\Students\Models\StudentDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentRepositoryInterface
{
    public function query(array $filters = []): Builder;

    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?StudentDocument;

    public function create(array $data): StudentDocument;

    public function update(int $id, array $data): StudentDocument;

    public function delete(int $id): bool;

    public function getExpiring(int $days = 30, int $limit = 10): Collection;

    public function getRecent(int $limit = 10): Collection;

    public function getPendingCount(): int;

    public function getExpiringCount(int $days = 30): int;
}
