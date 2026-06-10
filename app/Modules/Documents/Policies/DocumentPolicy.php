<?php

namespace App\Modules\Documents\Policies;

use App\Models\User;
use App\Modules\Students\Models\StudentDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('student_documents.view');
    }

    public function view(User $user, StudentDocument $document): bool
    {
        return $user->can('student_documents.view') && $document->school_id === $user->current_school_id;
    }

    public function create(User $user): bool
    {
        return $user->can('student_documents.create');
    }

    public function update(User $user, StudentDocument $document): bool
    {
        return $user->can('student_documents.update') && $document->school_id === $user->current_school_id;
    }

    public function delete(User $user, StudentDocument $document): bool
    {
        return $user->can('student_documents.delete') && $document->school_id === $user->current_school_id;
    }

    public function verify(User $user, StudentDocument $document): bool
    {
        return $user->can('student_documents.verify') && $document->school_id === $user->current_school_id;
    }

    public function download(User $user, StudentDocument $document): bool
    {
        return $user->can('student_documents.view') && $document->school_id === $user->current_school_id;
    }
}
