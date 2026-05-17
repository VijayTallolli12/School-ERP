<?php

namespace App\Modules\Settings\Repositories;

use App\Models\School;

interface SettingsRepositoryInterface
{
    public function currentSchool(): School;

    public function update(School $school, array $attributes, array $settings): School;
}
