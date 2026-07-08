<?php

namespace App\Modules\Teachers\Policies;

use App\Models\User;
use App\Modules\Teachers\Models\TeacherDocument;

class TeacherDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('Teacher')) {
            return true;
        }

        return $user->can('student_documents.view');
    }

    public function view(User $user, TeacherDocument $document): bool
    {
        if ($user->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::query()->where('user_id', $user->getKey())->first();

            return $teacher && $document->teacher_id === $teacher->id;
        }

        return $user->can('student_documents.view');
    }

    public function download(User $user, TeacherDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
