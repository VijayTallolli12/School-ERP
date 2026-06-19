<?php

namespace App\Modules\Library\Policies;

use App\Models\User;

class BookPolicy
{
    public function viewAny(User $user): bool { return $user->can('library.view'); }
    public function create(User $user): bool { return $user->can('library.create'); }
    public function update(User $user, $book): bool { return $user->can('library.update'); }
    public function delete(User $user, $book): bool { return $user->can('library.delete'); }
}
