<?php

namespace App\Modules\Fees\Repositories;

use App\Modules\Fees\Models\FeeCategory;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Models\StudentFee;
use App\Modules\Fees\Models\StudentFeeItem;
use Illuminate\Database\Eloquent\Builder;

class FeeRepository implements FeeRepositoryInterface
{
    public function feeCategoriesQuery(): Builder
    {
        return FeeCategory::query()->orderBy('sort_order')->orderBy('name');
    }

    public function feeStructuresQuery(): Builder
    {
        return FeeStructure::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'items.feeCategory']);
    }

    public function studentFeesQuery(): Builder
    {
        return StudentFee::query()
            ->with([
                'student',
                'academicYear',
                'feeStructure.classSection.schoolClass',
                'feeStructure.classSection.section',
                'items.feeCategory',
            ]);
    }

    public function feePaymentsQuery(): Builder
    {
        return FeePayment::query()
            ->with(['student', 'academicYear', 'collector', 'items.studentFeeItem.feeCategory']);
    }

    public function studentFeeItemsDueBaseQuery(): Builder
    {
        return StudentFeeItem::query()
            ->with([
                'feeCategory',
                'studentFee.student.sessions.classSection.schoolClass',
                'studentFee.student.sessions.classSection.section',
                'studentFee.academicYear',
            ])
            ->withSum(['paymentItems as paid_sum' => function ($q): void {
                $q->whereHas('feePayment');
            }], 'amount');
    }
}
