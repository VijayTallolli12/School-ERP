<?php

namespace App\Modules\Exams\Repositories;

use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ExamRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Exam;

    public function update(Exam $exam, array $data): Exam;

    public function delete(Exam $exam): void;

    public function resultsQuery(Exam $exam): HasMany;

    public function createResult(array $data): ExamResult;

    public function updateResult(ExamResult $result, array $data): ExamResult;

    public function deleteResult(ExamResult $result): void;
}
