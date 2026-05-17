<?php

namespace App\Modules\Teachers\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Teachers\Models\TeacherDocument;
use App\Modules\Teachers\Models\TeacherLeave;
use App\Modules\Teachers\Repositories\TeacherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class TeacherService
{
    public function __construct(private readonly TeacherRepositoryInterface $teachers) {}

    public function create(array $data, ?UploadedFile $photo = null): Teacher
    {
        return DB::transaction(function () use ($data, $photo): Teacher {
            $schoolId = app(SchoolContext::class)->id();
            $user = $this->createOrUpdateUser(null, $data, $schoolId);

            $teacherData = $this->teacherPayload($data);
            $teacherData['school_id'] = $schoolId;
            $teacherData['user_id'] = $user?->id;
            $teacherData['created_by'] = auth()->id();

            if ($photo) {
                $teacherData['photo_path'] = $photo->store('teachers/photos', 'public');
            }

            $teacher = $this->teachers->create($teacherData);
            $this->syncAssignments($teacher, $data);
            $this->syncDocuments($teacher, $data);

            activity()->causedBy(auth()->user())->performedOn($teacher)->event('created')->log('Teacher profile created');

            return $teacher->load($this->relations());
        });
    }

    public function update(Teacher $teacher, array $data, ?UploadedFile $photo = null): Teacher
    {
        return DB::transaction(function () use ($teacher, $data, $photo): Teacher {
            $schoolId = app(SchoolContext::class)->id();
            $user = $this->createOrUpdateUser($teacher, $data, $schoolId);

            $teacherData = $this->teacherPayload($data);
            $teacherData['updated_by'] = auth()->id();
            $teacherData['user_id'] = $user?->id ?? $teacher->user_id;

            if ($photo) {
                $teacherData['photo_path'] = $photo->store('teachers/photos', 'public');
            }

            $teacher = $this->teachers->update($teacher, $teacherData);
            $this->syncAssignments($teacher, $data);
            $this->syncDocuments($teacher, $data);

            activity()->causedBy(auth()->user())->performedOn($teacher)->event('updated')->log('Teacher profile updated');

            return $teacher->load($this->relations());
        });
    }

    public function delete(Teacher $teacher): void
    {
        $this->teachers->delete($teacher);
        activity()->causedBy(auth()->user())->performedOn($teacher)->event('deleted')->log('Teacher profile deleted');
    }

    public function getDashboardStats(): array
    {
        return [
            'total' => Teacher::query()->count(),
            'active' => Teacher::query()->where('status', 'active')->count(),
            'attendance_today' => TeacherAttendance::query()->whereDate('attendance_date', today())->count(),
        ];
    }

    public function getSubjectAllocationReport(): Collection
    {
        return Teacher::query()
            ->with(['subjects', 'classSections.schoolClass', 'classSections.section'])
            ->orderBy('first_name')
            ->get();
    }

    public function getAttendanceReport(array $filters): Collection
    {
        $query = TeacherAttendance::query()->with(['teacher'])->orderByDesc('attendance_date');

        if (! empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['to_date']);
        }

        return $query->get();
    }

    public function recordAttendance(array $data): TeacherAttendance
    {
        $attendance = TeacherAttendance::query()->create([
            'teacher_id' => $data['teacher_id'],
            'attendance_date' => $data['attendance_date'],
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'marked_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($attendance)->event('created')->log('Teacher attendance recorded');

        return $attendance;
    }

    public function updateAttendance(TeacherAttendance $attendance, array $data): TeacherAttendance
    {
        $attendance->fill([
            'attendance_date' => $data['attendance_date'],
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
        ])->save();

        activity()->causedBy(auth()->user())->performedOn($attendance)->event('updated')->log('Teacher attendance updated');

        return $attendance->refresh();
    }

    public function deleteAttendance(TeacherAttendance $attendance): void
    {
        $attendance->delete();
        activity()->causedBy(auth()->user())->performedOn($attendance)->event('deleted')->log('Teacher attendance deleted');
    }

    public function requestLeave(array $data): TeacherLeave
    {
        $leave = TeacherLeave::query()->create([
            'teacher_id' => $data['teacher_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'approved_by' => $data['status'] !== 'pending' ? auth()->id() : null,
            'approved_at' => $data['status'] !== 'pending' ? now() : null,
            'remarks' => $data['remarks'] ?? null,
        ]);

        activity()->causedBy(auth()->user())->performedOn($leave)->event('created')->log('Teacher leave requested');

        return $leave;
    }

    public function updateLeave(TeacherLeave $leave, array $data): TeacherLeave
    {
        $approvedBy = $leave->approved_by;
        $approvedAt = $leave->approved_at;

        if (! empty($data['status']) && in_array($data['status'], ['approved', 'rejected'], true)) {
            $approvedBy = auth()->id();
            $approvedAt = now();
        }

        $leave->fill([
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
            'status' => $data['status'] ?? $leave->status,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
            'remarks' => $data['remarks'] ?? null,
        ])->save();

        activity()->causedBy(auth()->user())->performedOn($leave)->event('updated')->log('Teacher leave updated');

        return $leave->refresh();
    }

    public function deleteLeave(TeacherLeave $leave): void
    {
        $leave->delete();
        activity()->causedBy(auth()->user())->performedOn($leave)->event('deleted')->log('Teacher leave deleted');
    }

    private function createOrUpdateUser(?Teacher $teacher, array $data, ?int $schoolId): ?User
    {
        if (empty($data['create_user'])) {
            return $teacher?->user;
        }

        $payload = [
            'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'status' => 'active',
            'current_school_id' => $schoolId,
            'email_verified_at' => now(),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($teacher?->user) {
            $teacher->user->update(array_filter($payload));
            $user = $teacher->user;
        } else {
            $user = User::query()->create($payload);
        }

        if ($schoolId) {
            $user->schools()->syncWithoutDetaching([
                $schoolId => [
                    'designation' => 'Teacher',
                    'employee_code' => $data['employee_id'] ?? null,
                    'joined_at' => $data['joining_date'] ?? now()->toDateString(),
                    'status' => 'active',
                    'is_primary' => false,
                ],
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
            $user->assignRole('Teacher');
        }

        return $user;
    }

    private function teacherPayload(array $data): array
    {
        return Arr::only($data, [
            'employee_id',
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'date_of_birth',
            'qualification',
            'experience_years',
            'joining_date',
            'phone',
            'email',
            'address',
            'status',
        ]);
    }

    private function syncAssignments(Teacher $teacher, array $data): void
    {
        $subjectIds = array_filter(Arr::wrap($data['subject_ids'] ?? []));
        $classSectionIds = array_filter(Arr::wrap($data['class_section_ids'] ?? []));
        $classTeacherSectionIds = array_filter(Arr::wrap($data['class_teacher_section_ids'] ?? []));

        $teacher->subjects()->sync($subjectIds);

        $sync = [];
        foreach (array_unique(array_merge($classSectionIds, $classTeacherSectionIds)) as $classSectionId) {
            $sync[$classSectionId] = ['is_class_teacher' => in_array($classSectionId, $classTeacherSectionIds, true)];
        }

        $teacher->classSections()->sync($sync);
    }

    private function syncDocuments(Teacher $teacher, array $data): void
    {
        if (! empty($data['certificates']) && is_array($data['certificates'])) {
            foreach ($data['certificates'] as $certificate) {
                if ($certificate instanceof UploadedFile) {
                    $teacher->documents()->create([
                        'document_type' => 'certificate',
                        'file_path' => $certificate->store('teachers/documents', 'public'),
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }

        if (! empty($data['id_proofs']) && is_array($data['id_proofs'])) {
            foreach ($data['id_proofs'] as $file) {
                if ($file instanceof UploadedFile) {
                    $teacher->documents()->create([
                        'document_type' => 'id_proof',
                        'file_path' => $file->store('teachers/documents', 'public'),
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }
    }

    private function relations(): array
    {
        return [
            'subjects',
            'classSections.schoolClass',
            'classSections.section',
            'classTeacherSections.schoolClass',
            'classTeacherSections.section',
            'documents',
        ];
    }
}
