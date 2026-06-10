<?php

namespace App\Modules\Documents\Repositories;

use App\Modules\Students\Models\StudentDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function query(array $filters = []): Builder
    {
        $query = StudentDocument::with(['student.sessions.classSection.schoolClass', 'uploader', 'verifier']);

        if (! empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (! empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (! empty($filters['class_id'])) {
            $query->whereHas('student.sessions', fn ($q) => $q->where('school_class_id', $filters['class_id'])->where('status', 'active'));
        }

        if (isset($filters['is_verified']) && $filters['is_verified'] !== '') {
            $query->where('is_verified', filter_var($filters['is_verified'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['expiry_from'])) {
            $query->whereNotNull('expiry_date')->where('expiry_date', '<=', $filters['expiry_from']);
        }

        if (! empty($filters['expiry_to'])) {
            $query->whereNotNull('expiry_date')->where('expiry_date', '>=', $filters['expiry_to']);
        }



        return $query;
    }

    public function paginate(int $perPage = 25, array $filters = []): LengthAwarePaginator
    {
        return $this->query($filters)->latest()->paginate($perPage);
    }

    public function find(int $id): ?StudentDocument
    {
        return StudentDocument::with(['student', 'uploader', 'verifier'])->find($id);
    }

    public function create(array $data): StudentDocument
    {
        return StudentDocument::create($data);
    }

    public function update(int $id, array $data): StudentDocument
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

    public function getExpiring(int $days = 30, int $limit = 10): Collection
    {
        return StudentDocument::with(['student', 'uploader'])
            ->expiring($days)
            ->latest('expiry_date')
            ->limit($limit)
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return StudentDocument::with(['student', 'uploader'])
            ->recent($limit)
            ->get();
    }

    public function getPendingCount(): int
    {
        return StudentDocument::pending()->count();
    }

    public function getExpiringCount(int $days = 30): int
    {
        return StudentDocument::expiring($days)->count();
    }
}
