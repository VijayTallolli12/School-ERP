<?php

namespace App\Modules\Admissions\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Admissions\Models\Admission;
use App\Modules\Admissions\Models\AdmissionDocument;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Services\StudentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AdmissionService
{
    public function __construct(
        private readonly StudentService $students,
        private readonly FeeService $fees,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function create(array $data, ?UploadedFile $photo = null): Admission
    {
        return DB::transaction(function () use ($data, $photo): Admission {
            $data['school_id'] = $this->schoolContext->id();
            $data['status'] ??= 'enquiry';
            $data['applied_on'] ??= now()->toDateString();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $admission = Admission::query()->create($data);

            if ($photo) {
                $admission->documents()->create([
                    'school_id' => $admission->school_id,
                    'document_type' => 'photo',
                    'document_name' => 'Applicant Photo',
                    'file_path' => $photo->store('admissions/photos', 'public'),
                    'verified' => false,
                    'created_by' => auth()->id(),
                ]);
            }

            activity()->causedBy(auth()->user())->performedOn($admission)->event('created')->log('Admission application created');

            return $admission->load($this->relations());
        });
    }

    public function update(Admission $admission, array $data): Admission
    {
        $data['updated_by'] = auth()->id();
        $admission->fill($data)->save();

        activity()->causedBy(auth()->user())->performedOn($admission)->event('updated')->log('Admission application updated');

        return $admission->load($this->relations());
    }

    public function submitApplication(Admission $admission): Admission
    {
        if (! in_array($admission->status, ['enquiry'], true)) {
            throw new RuntimeException('Only enquiry records can be submitted as applications.');
        }

        $admission->update([
            'status' => 'application',
            'updated_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($admission)->event('updated')->log('Admission application submitted');

        return $admission;
    }

    public function verify(Admission $admission): Admission
    {
        if (! in_array($admission->status, ['enquiry', 'application'], true)) {
            throw new RuntimeException('Application must be an enquiry or submitted before verification.');
        }

        $admission->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($admission)->event('verified')->log('Admission application verified');

        return $admission;
    }

    public function approve(Admission $admission): Admission
    {
        if ($admission->status === 'converted') {
            throw new RuntimeException('This application has already been converted to a student.');
        }

        if (in_array($admission->status, ['rejected'], true)) {
            throw new RuntimeException('A rejected application cannot be approved.');
        }

        $admission->update([
            'status' => 'approved',
            'admission_no' => $admission->admission_no ?? $this->generateAdmissionNo(),
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($admission)->event('approved')->log('Admission application approved');

        return $admission;
    }

    public function reject(Admission $admission, string $reason): Admission
    {
        if ($admission->status === 'converted') {
            throw new RuntimeException('This application has already been converted to a student.');
        }

        $admission->update([
            'status' => 'rejected',
            'remarks' => $reason ?: $admission->remarks,
            'updated_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($admission)->event('rejected')->log('Admission application rejected');

        return $admission;
    }

    public function convertToStudent(Admission $admission): Student
    {
        return DB::transaction(function () use ($admission): Student {
            if (! in_array($admission->status, ['approved', 'verified'], true)) {
                throw new RuntimeException('Only approved or verified applications can be converted to students.');
            }

            if ($admission->student_id) {
                throw new RuntimeException('This application has already been converted.');
            }

            if (! $admission->class_section_id || ! $admission->academic_year_id) {
                throw new RuntimeException('Class and academic year are required before conversion.');
            }

            $student = $this->students->create($this->studentPayload($admission));

            $admission->update([
                'status' => 'converted',
                'student_id' => $student->id,
                'admission_no' => $admission->admission_no ?? $student->admission_no,
                'converted_at' => now(),
                'converted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->assignFeeIfAvailable($student);
            $this->sendWelcomeNotification($admission, $student);

            activity()->causedBy(auth()->user())->performedOn($admission)->event('converted')->log('Admission application converted to student '.$student->full_name);

            return $student->load(['guardians', 'sessions.academicYear', 'sessions.classSection.schoolClass', 'sessions.classSection.section']);
        });
    }

    public function addDocument(Admission $admission, string $type, ?string $name, ?UploadedFile $file): AdmissionDocument
    {
        if (! $file) {
            throw new RuntimeException('A document file is required.');
        }

        $document = $admission->documents()->create([
            'school_id' => $admission->school_id,
            'document_type' => $type,
            'document_name' => $name ?: $file->getClientOriginalName(),
            'file_path' => $file->store('admissions/documents', 'public'),
            'verified' => false,
            'created_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($document)->event('created')->log('Admission document uploaded');

        return $document;
    }

    public function verifyDocument(AdmissionDocument $document): AdmissionDocument
    {
        $document->update([
            'verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        activity()->causedBy(auth()->user())->performedOn($document)->event('verified')->log('Admission document verified');

        return $document;
    }

    public function deleteDocument(AdmissionDocument $document): void
    {
        activity()->causedBy(auth()->user())->performedOn($document)->event('deleted')->log('Admission document deleted');
        $document->delete();
    }

    public function stats(): array
    {
        $base = Admission::query()->where('school_id', $this->schoolContext->id());

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (clone $base)->count(),
            'enquiry' => (int) ($statusCounts['enquiry'] ?? 0),
            'application' => (int) ($statusCounts['application'] ?? 0),
            'verified' => (int) ($statusCounts['verified'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'rejected' => (int) ($statusCounts['rejected'] ?? 0),
            'converted' => (int) ($statusCounts['converted'] ?? 0),
            'pending_documents' => (int) AdmissionDocument::query()
                ->where('school_id', $this->schoolContext->id())
                ->where('verified', false)
                ->count(),
        ];
    }

    private function studentPayload(Admission $admission): array
    {
        return [
            'admission_no' => $admission->admission_no ?? $this->generateAdmissionNo(),
            'admission_date' => $admission->applied_on?->toDateString() ?? now()->toDateString(),
            'first_name' => $admission->first_name,
            'middle_name' => $admission->middle_name,
            'last_name' => $admission->last_name,
            'date_of_birth' => $admission->date_of_birth?->toDateString(),
            'gender' => $admission->gender,
            'blood_group' => $admission->blood_group,
            'religion' => $admission->religion,
            'category' => $admission->category,
            'caste' => $admission->caste,
            'nationality' => $admission->nationality,
            'mother_tongue' => $admission->mother_tongue,
            'aadhar_no' => $admission->aadhar_no,
            'current_address' => $admission->current_address,
            'permanent_address' => $admission->permanent_address,
            'status' => 'active',
            'academic_year_id' => $admission->academic_year_id,
            'class_section_id' => $admission->class_section_id,
            'guardian_name' => $admission->guardian_name,
            'guardian_relation' => $admission->guardian_relation,
            'guardian_phone' => $admission->guardian_phone,
            'guardian_email' => $admission->guardian_email,
            'guardian_occupation' => $admission->guardian_occupation,
        ];
    }

    private function assignFeeIfAvailable(Student $student): void
    {
        $session = $student->sessions()->where('status', 'active')->latest()->first();

        if (! $session) {
            return;
        }

        $structure = FeeStructure::query()
            ->where('school_id', $student->school_id)
            ->where('academic_year_id', $session->academic_year_id)
            ->where('class_section_id', $session->class_section_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $structure) {
            return;
        }

        try {
            $this->fees->assignStudentFee([
                'student_id' => $student->id,
                'academic_year_id' => $session->academic_year_id,
                'fee_structure_id' => $structure->id,
            ]);
        } catch (RuntimeException $e) {
            Log::warning('Auto fee assignment skipped for student '.$student->id.': '.$e->getMessage());
        }
    }

    private function sendWelcomeNotification(Admission $admission, Student $student): void
    {
        $recipientIds = [];

        foreach ($student->guardians as $guardian) {
            if (! empty($guardian->user_id)) {
                $recipientIds[$guardian->user_id] = true;
            }
        }

        if (! empty($student->user_id)) {
            $recipientIds[$student->user_id] = true;
        }

        if ($recipientIds === []) {
            return;
        }

        $notification = Notification::query()->create([
            'school_id' => $student->school_id,
            'title' => 'Admission Confirmed - '.$student->full_name,
            'message' => 'Congratulations! Your admission to '.setting('school_name', 'our school').' has been confirmed. Admission No: '.$student->admission_no.'. Please login to the portal for further details.',
            'type' => 'announcement',
            'priority' => 'high',
            'status' => 'sent',
            'target_type' => 'parents',
            'channel' => 'in_app',
            'sent_at' => now(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach (array_keys($recipientIds) as $userId) {
            $notification->users()->attach($userId, [
                'is_read' => false,
                'delivery_status' => 'delivered',
            ]);
        }
    }

    private function generateAdmissionNo(): string
    {
        $schoolId = $this->schoolContext->id();
        $year = now()->format('Y');

        $next = (int) Admission::query()
            ->withTrashed()
            ->where('school_id', $schoolId)
            ->whereYear('created_at', now()->year)
            ->max('admission_no') ?: 0;

        do {
            $next++;
            $candidate = sprintf('ADM-%s-%04d', $year, $next);
        } while (Admission::query()->where('school_id', $schoolId)->where('admission_no', $candidate)->exists());

        return $candidate;
    }

    private function relations(): array
    {
        return [
            'student',
            'classSection.schoolClass',
            'classSection.section',
            'academicYear',
            'documents',
            'creator',
        ];
    }
}
