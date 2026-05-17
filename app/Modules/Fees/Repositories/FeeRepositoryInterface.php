<?php

namespace App\Modules\Fees\Repositories;

use Illuminate\Database\Eloquent\Builder;

interface FeeRepositoryInterface
{
    public function feeCategoriesQuery(): Builder;

    public function feeStructuresQuery(): Builder;

    public function studentFeesQuery(): Builder;

    public function feePaymentsQuery(): Builder;

    public function studentFeeItemsDueBaseQuery(): Builder;
}
