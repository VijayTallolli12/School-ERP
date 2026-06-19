<?php

namespace App\Modules\Library\Policies;

use App\Models\User;

class BookIssuePolicy
{
    public function viewAny(User $user): bool { return $user->can('library.view'); }
    public function create(User $user): bool { return $user->can('library.create'); }
    public function update(User $user, $issue): bool { return $user->can('library.update'); }
    public function delete(User $user, $issue): bool { return $user->can('library.delete'); }
}
