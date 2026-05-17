<?php

namespace App\Modules\Students\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentGuardian;
use App\Modules\Students\Repositories\StudentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StudentService
{
    private const PARENT_RELATIONSHIPS = ['father', 'mother', 'guardian', 'other'];

    public function __construct(private readonly StudentRepositoryInterface $students) {}

    public function create(array $data, ?UploadedFile $photo = null): Student
    {
        return DB::transaction(function () use ($data, $photo): Student {
            $schoolId = app(SchoolContext::class)->id();
            $user = $this->createUserIfRequested($data, $schoolId);

            $studentData = $this->studentPayload($data);
            $studentData['school_id'] = $schoolId;
            $studentData['user_id'] = $user?->id;
            $studentData['created_by'] = auth()->id();

            if ($photo) {
                $studentData['photo_path'] = $photo->store('students/photos', 'public');
            }

            $student = $this->students->create($studentData);
            $this->syncSession($student, $data);
            $this->syncGuardians($student, $data);

            activity()->causedBy(auth()->user())->performedOn($student)->event('created')->log('Student admitted');

            return $student->load($this->relations());
        });
    }

    public function update(Student $student, array $data, ?UploadedFile $photo = null): Student
    {
        return DB::transaction(function () use ($student, $data, $photo): Student {
            $studentData = $this->studentPayload($data);
            $studentData['updated_by'] = auth()->id();

            if ($photo) {
                $studentData['photo_path'] = $photo->store('students/photos', 'public');
            }

            $student = $this->students->update($student, $studentData);
            $this->syncSession($student, $data);
            $this->syncGuardians($student, $data);

            activity()->causedBy(auth()->user())->performedOn($student)->event('updated')->log('Student updated');

            return $student->load($this->relations());
        });
    }

    public function delete(Student $student): void
    {
        $this->students->delete($student);
        activity()->causedBy(auth()->user())->performedOn($student)->event('deleted')->log('Student deleted');
    }

    private function createUserIfRequested(array $data, ?int $schoolId): ?User
    {
        if (empty($data['create_user'])) {
            return null;
        }

        $user = User::query()->create([
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'current_school_id' => $schoolId,
            'email_verified_at' => now(),
        ]);

        if ($schoolId) {
            $user->schools()->syncWithoutDetaching([
                $schoolId => [
                    'designation' => 'Student',
                    'joined_at' => now()->toDateString(),
                    'status' => 'active',
                    'is_primary' => true,
                ],
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($schoolId);
            $user->assignRole('Student');
        }

        return $user;
    }

    private function studentPayload(array $data): array
    {
        return Arr::only($data, [
            'admission_no',
            'admission_date',
            'first_name',
            'middle_name',
            'last_name',
            'date_of_birth',
            'gender',
            'blood_group',
            'religion',
            'category',
            'caste',
            'nationality',
            'mother_tongue',
            'aadhar_no',
            'current_address',
            'permanent_address',
            'status',
        ]);
    }

    private function syncSession(Student $student, array $data): void
    {
        $student->sessions()->updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'student_id' => $student->id,
            ],
            [
                'school_id' => $student->school_id,
                'class_section_id' => $data['class_section_id'],
                'roll_no' => $data['roll_no'] ?? null,
                'joined_on' => $data['admission_date'] ?? now()->toDateString(),
                'status' => 'active',
            ],
        );
    }

    private function syncGuardians(Student $student, array $data): void
    {
        $guardians = $this->guardianPayloads($data);

        if ($guardians === []) {
            return;
        }

        $syncedGuardianIds = [];

        foreach ($guardians as $index => $guardianData) {
            $guardian = $this->findExistingStudentGuardian($student, $guardianData, count($guardians) === 1);

            if ($guardian?->trashed()) {
                $guardian->restore();
            }

            $payload = [
                'school_id' => $student->school_id,
                'relation' => $guardianData['relation'],
                'name' => $guardianData['name'],
                'phone' => $guardianData['phone'],
                'email' => $guardianData['email'] ?? null,
                'occupation' => $guardianData['occupation'] ?? null,
                'is_primary' => (bool) ($guardianData['is_primary'] ?? $index === 0),
                'can_pickup' => (bool) ($guardianData['can_pickup'] ?? true),
            ];

            if ($guardian) {
                $guardian->fill($payload)->save();
            } else {
                $guardian = $student->guardians()->create($payload);
            }

            $syncedGuardianIds[] = $guardian->id;
        }

        $this->normalizePrimaryGuardian($student, $syncedGuardianIds);

        if (isset($data['guardians']) && is_array($data['guardians'])) {
            $student->guardians()
                ->whereNotIn('id', $syncedGuardianIds)
                ->delete();
        }

        DB::table('parent_student')
            ->where('student_id', $student->id)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $student->load('guardians');

        foreach ($student->guardians as $guardian) {
            $this->syncParentFromGuardian($student, $guardian);
        }
    }

    private function guardianPayloads(array $data): array
    {
        if (isset($data['guardians']) && is_array($data['guardians'])) {
            return collect($data['guardians'])
                ->map(fn (array $guardian): array => [
                    'id' => $guardian['id'] ?? null,
                    'relation' => $guardian['relation'] ?? null,
                    'name' => $guardian['name'] ?? null,
                    'phone' => $guardian['phone'] ?? null,
                    'email' => $guardian['email'] ?? null,
                    'occupation' => $guardian['occupation'] ?? null,
                    'is_primary' => filter_var($guardian['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'can_pickup' => filter_var($guardian['can_pickup'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ])
                ->filter(fn (array $guardian): bool => filled($guardian['name']) || filled($guardian['phone']) || filled($guardian['email']))
                ->values()
                ->all();
        }

        if (empty($data['guardian_name']) && empty($data['guardian_phone']) && empty($data['guardian_email'])) {
            return [];
        }

        return [[
            'relation' => $data['guardian_relation'] ?? 'guardian',
            'name' => $data['guardian_name'] ?? '',
            'phone' => $data['guardian_phone'] ?? '',
            'email' => $data['guardian_email'] ?? null,
            'occupation' => $data['guardian_occupation'] ?? null,
            'is_primary' => true,
            'can_pickup' => true,
        ]];
    }

    private function findExistingStudentGuardian(Student $student, array $guardianData, bool $allowPrimaryFallback): ?StudentGuardian
    {
        $query = $student->guardians()->withTrashed();

        if (!empty($guardianData['id'])) {
            return (clone $query)->whereKey($guardianData['id'])->first();
        }

        if (!empty($guardianData['email'])) {
            return (clone $query)->where('email', $guardianData['email'])->first();
        }

        if (!empty($guardianData['phone']) && !empty($guardianData['relation'])) {
            return (clone $query)
                ->where('phone', $guardianData['phone'])
                ->where('relation', $guardianData['relation'])
                ->first();
        }

        if ($allowPrimaryFallback) {
            return (clone $query)->where('is_primary', true)->first();
        }

        return null;
    }

    private function normalizePrimaryGuardian(Student $student, array $syncedGuardianIds): void
    {
        if ($syncedGuardianIds === []) {
            return;
        }

        $syncedGuardians = $student->guardians()
            ->whereIn('id', $syncedGuardianIds)
            ->get()
            ->sortBy(fn (StudentGuardian $guardian): int => array_search($guardian->id, $syncedGuardianIds, true));

        $primaryId = $syncedGuardians->firstWhere('is_primary', true)?->id ?? $syncedGuardianIds[0];

        $student->guardians()
            ->whereIn('id', $syncedGuardianIds)
            ->update(['is_primary' => false]);

        $student->guardians()
            ->whereKey($primaryId)
            ->update(['is_primary' => true]);
    }

    private function syncPrimaryGuardian(Student $student, array $data): void
    {
        $guardian = $student->guardians()->updateOrCreate(
            ['is_primary' => true],
            [
                'school_id' => $student->school_id,
                'relation' => $data['guardian_relation'],
                'name' => $data['guardian_name'],
                'phone' => $data['guardian_phone'],
                'email' => $data['guardian_email'] ?? null,
                'occupation' => $data['guardian_occupation'] ?? null,
                'is_primary' => true,
                'can_pickup' => true,
            ],
        );

        $this->syncParentFromGuardian($student, $guardian);
    }

    private function syncParentFromGuardian(Student $student, StudentGuardian $guardian): void
    {
        if (empty($guardian->email) && empty($guardian->phone)) {
            return;
        }

        $query = Guardian::query()->where('school_id', $student->school_id);

        $query->where(function ($query) use ($guardian): void {
            if (!empty($guardian->email)) {
                $query->orWhere('email', $guardian->email);
            }

            if (!empty($guardian->phone)) {
                $query->orWhere('phone', $guardian->phone);
            }
        });

        $parent = $query->first();

        if (!$parent && empty($guardian->email)) {
            return;
        }

        $nameParts = $this->splitGuardianName($guardian->name);

        $parentData = [
            'school_id' => $student->school_id,
            'first_name' => $nameParts['first_name'],
            'last_name' => $nameParts['last_name'],
            'email' => $guardian->email,
            'phone' => $guardian->phone,
            'occupation' => $guardian->occupation,
            'status' => 'active',
            'updated_by' => auth()->id(),
        ];

        if ($parent) {
            $parent->update(array_filter($parentData, fn ($value) => $value !== null));
        } else {
            $parentData['created_by'] = auth()->id();
            $parent = Guardian::create($parentData);
        }

        if (!empty($guardian->email)) {
            $user = User::where('email', $guardian->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $guardian->name,
                    'email' => $guardian->email,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'current_school_id' => $student->school_id,
                    'email_verified_at' => now(),
                ]);
            }

            if (!$parent->user_id) {
                $parent->update(['user_id' => $user->id]);
            }

            if (!$guardian->user_id) {
                $guardian->update(['user_id' => $user->id]);
            }

            if (Role::where('name', 'Parent')->exists() && !$user->hasRole('Parent')) {
                $user->assignRole('Parent');
            }
        }

        $parent->students()->syncWithoutDetaching([
            $student->id => [
                'relationship' => $this->normalizeParentRelationship($guardian->relation),
                'is_primary' => $guardian->is_primary,
            ],
        ]);
    }

    private function normalizeParentRelationship(?string $relation): string
    {
        $normalized = Str::of((string) $relation)->lower()->trim()->toString();

        return in_array($normalized, self::PARENT_RELATIONSHIPS, true)
            ? $normalized
            : 'other';
    }

    private function splitGuardianName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) <= 1) {
            return ['first_name' => $parts[0] ?? '', 'last_name' => ''];
        }

        return [
            'first_name' => array_shift($parts),
            'last_name' => implode(' ', $parts),
        ];
    }

    private function relations(): array
    {
        return [
            'guardians',
            'sessions.academicYear',
            'sessions.classSection.schoolClass',
            'sessions.classSection.section',
        ];
    }
}
