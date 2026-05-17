<?php

namespace App\Modules\Academics\Policies;

use App\Models\User;
use App\Modules\Academics\Models\Section;

class SectionPolicy
{
    public function viewAny(User $user): bool { return $user->can('academics.view'); }

    public function create(User $user): bool { return $user->can('academics.create'); }

    public function update(User $user, Section $section): bool { return $user->can('academics.update'); }

    public function delete(User $user, Section $section): bool { return $user->can('academics.delete') && ! $section->classes()->exists(); }
}
