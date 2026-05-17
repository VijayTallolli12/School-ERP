<?php

namespace Database\Seeders;

use App\Models\School;
use App\Modules\Fees\Models\FeeCategory;
use Illuminate\Database\Seeder;

class FeeCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (School::query()->withoutGlobalScopes()->get() as $school) {
            $order = 0;
            foreach (FeeCategory::defaultCodes() as $code => $name) {
                FeeCategory::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'code' => $code,
                    ],
                    [
                        'name' => $name,
                        'description' => null,
                        'sort_order' => $order++,
                    ]
                );
            }
        }
    }
}
