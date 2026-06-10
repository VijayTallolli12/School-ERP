<?php

namespace App\Modules\Documents\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Documents\Repositories\DocumentRepositoryInterface;
use App\Modules\Students\Models\StudentDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $repository,
        private readonly DocumentUploadService $uploadService,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function create(array $data, ?UploadedFile $file): StudentDocument
    {
        return DB::transaction(function () use ($data, $file): StudentDocument {
            $schoolId = $this->schoolContext->id();
            $studentId = $data['student_id'];
            $directory = StudentDocument::uploadDirectory($schoolId, $studentId);

            $fileInfo = $this->uploadService->fileInfo($file);
            $data['file_path'] = $this->uploadService->upload($file, $directory);
            $data['file_name'] = $fileInfo['file_name'];
            $data['file_size'] = $fileInfo['file_size'];
            $data['mime_type'] = $fileInfo['mime_type'];
            $data['school_id'] = $schoolId;
            $data['uploaded_by'] = auth()->id();
            $data['is_verified'] = false;

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data, ?UploadedFile $file): StudentDocument
    {
        return DB::transaction(function () use ($id, $data, $file): StudentDocument {
            $document = $this->repository->find($id);
            $data['updated_by'] = auth()->id();

            if ($file) {
                $this->uploadService->delete($document->file_path);

                $schoolId = $this->schoolContext->id();
                $studentId = $document->student_id;
                $directory = StudentDocument::uploadDirectory($schoolId, $studentId);

                $fileInfo = $this->uploadService->fileInfo($file);
                $data['file_path'] = $this->uploadService->upload($file, $directory);
                $data['file_name'] = $fileInfo['file_name'];
                $data['file_size'] = $fileInfo['file_size'];
                $data['mime_type'] = $fileInfo['mime_type'];
            }

            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $document = $this->repository->find($id);
            if (! $document) {
                return false;
            }

            $this->uploadService->delete($document->file_path);
            return $this->repository->delete($id);
        });
    }

    public function verify(int $id): StudentDocument
    {
        return DB::transaction(function () use ($id): StudentDocument {
            $data = [
                'is_verified' => true,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'updated_by' => auth()->id(),
            ];

            return $this->repository->update($id, $data);
        });
    }

    public function unverify(int $id): StudentDocument
    {
        return DB::transaction(function () use ($id): StudentDocument {
            $data = [
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
                'updated_by' => auth()->id(),
            ];

            return $this->repository->update($id, $data);
        });
    }

    public function find(int $id): ?StudentDocument
    {
        return $this->repository->find($id);
    }

    public function getExpiringDocuments(int $days = 30, int $limit = 6): array
    {
        return $this->repository->getExpiring($days, $limit)->toArray();
    }

    public function getRecentDocuments(int $limit = 6): array
    {
        return $this->repository->getRecent($limit)->toArray();
    }

    public function getPendingCount(): int
    {
        return $this->repository->getPendingCount();
    }

    public function getExpiringCount(int $days = 30): int
    {
        return $this->repository->getExpiringCount($days);
    }
}
