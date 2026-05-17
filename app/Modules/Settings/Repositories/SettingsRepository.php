<?php

namespace App\Modules\Settings\Repositories;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use RuntimeException;

class SettingsRepository implements SettingsRepositoryInterface
{
    public function __construct(private readonly SchoolContext $schoolContext) {}

    public function currentSchool(): School
    {
        $school = $this->schoolContext->school()
            ?: auth()->user()?->currentSchool
            ?: auth()->user()?->schools()->wherePivot('status', 'active')->first();

        if (! $school) {
            throw new RuntimeException('No active school is selected.');
        }

        return $school;
    }

    public function update(School $school, array $attributes, array $settings): School
    {
        $school->fill($attributes);
        $school->settings = $settings;
        $school->save();

        return $school->refresh();
    }
}
