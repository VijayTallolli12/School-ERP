<?php

namespace App\Modules\Homework\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Homework\Models\Homework;
use App\Modules\Homework\Repositories\HomeworkRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HomeworkService
{
    public function __construct(
        private readonly HomeworkRepositoryInterface $homework,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function create(array $data): Homework
    {
        return DB::transaction(function () use ($data): Homework {
            $payload = $this->payload($data);
            $payload['school_id'] = $this->schoolContext->id();
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();

            if ($attachment = $this->uploadAttachment($data)) {
                $payload['attachment'] = $attachment;
            }

            return $this->homework->create($payload);
        });
    }

    public function update(Homework $homework, array $data): Homework
    {
        return DB::transaction(function () use ($homework, $data): Homework {
            $payload = $this->payload($data);
            $payload['updated_by'] = auth()->id();

            if ($attachment = $this->uploadAttachment($data)) {
                $this->deleteAttachment($homework);
                $payload['attachment'] = $attachment;
            } elseif (($data['remove_attachment'] ?? false) && $homework->attachment) {
                $this->deleteAttachment($homework);
                $payload['attachment'] = null;
            }

            return $this->homework->update($homework, $payload);
        });
    }

    public function delete(Homework $homework): void
    {
        DB::transaction(function () use ($homework): void {
            $this->deleteAttachment($homework);
            $this->homework->delete($homework);
        });
    }

    private function payload(array $data): array
    {
        return Arr::only($data, [
            'academic_year_id',
            'class_section_id',
            'subject_id',
            'title',
            'description',
            'assigned_date',
            'due_date',
            'status',
        ]);
    }

    private function uploadAttachment(array $data): ?string
    {
        $file = $data['attachment'] ?? null;

        if (! ($file instanceof UploadedFile && $file->isValid())) {
            return null;
        }

        return $file->store('homework', 'public');
    }

    private function deleteAttachment(Homework $homework): void
    {
        if ($homework->attachment) {
            Storage::disk('public')->delete($homework->attachment);
        }
    }
}
