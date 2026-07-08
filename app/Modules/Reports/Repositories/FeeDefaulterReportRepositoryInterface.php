<?php

namespace App\Modules\Reports\Repositories;

use Illuminate\Support\Collection;

interface FeeDefaulterReportRepositoryInterface
{
    public function defaulters(
        ?int $academicYearId,
        ?int $classSectionId,
        ?int $studentId,
        ?int $feeStructureId,
        ?string $fromDueDate,
        ?string $toDueDate,
        ?float $minOutstanding,
        ?float $maxOutstanding
    ): array;

    public function getStudentsByClass(?int $classSectionId): Collection;
}
