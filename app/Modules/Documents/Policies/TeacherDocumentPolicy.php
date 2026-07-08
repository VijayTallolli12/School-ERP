<?php

namespace App\Modules\Documents\Policies;

use App\Models\User;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherDocument;

class TeacherDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Teacher');
    }

    public function view(User $user, TeacherDocument $document): bool
    {
        if (! $user->hasRole('Teacher')) {
            return $user->can('teacher_documents.view');
        }
        $teacher = Teacher::where('user_id', $user->id)->first();
        return $teacher && $document->teacher_id === $teacher->id;
    }

    public function create(User $user): bool
    {
        return $user->can('teacher_documents.create');
    }

    public function update(User $user, TeacherDocument $document): bool
    {
        return $user->can('teacher_documents.update');
    }

    public function delete(User $user, TeacherDocument $document): bool
    {
        return $user->can('teacher_documents.delete');
    }
}
