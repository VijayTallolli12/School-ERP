<?php

namespace App\Modules\Reports\Services;

use App\Modules\Reports\Repositories\StudentReportRepositoryInterface;

class StudentReportService
{
    protected $studentReportRepository;

    public function __construct(StudentReportRepositoryInterface $studentReportRepository)
    {
        $this->studentReportRepository = $studentReportRepository;
    }

    public function getStudentListData($filters = [])
    {
        return $this->studentReportRepository->getStudentListQuery($filters);
    }

    public function getAdmissionReportData($filters = [])
    {
        return $this->studentReportRepository->getAdmissionReportData($filters);
    }

    public function getClassWiseReportData($filters = [])
    {
        return $this->studentReportRepository->getClassWiseReportData($filters);
    }

    public function formatStudentRow($student): object
    {
        $session = $student->sessions->first();

        $fullName = trim(optional($student->user)->first_name . ' ' . optional($student->user)->last_name);
        $fullName = $fullName ?: ($student->full_name ?: 'Unknown Student');

        $classSection = $session && $session->classSection
            ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name
            : '';

        $guardianNames = $student->guardians->map(function ($guardian) {
            return optional($guardian->user)->first_name ?: $guardian->name;
        })->filter();

        if ($guardianNames->isEmpty()) {
            $guardianNames = $student->parents->map(fn ($p) => $p->full_name)->filter();
        }

        return (object) [
            'full_name' => $fullName,
            'admission_no' => $student->admission_no,
            'class_section' => $classSection,
            'guardian' => $guardianNames->join(', ') ?: '-',
            'actions' => '<a href="#" class="btn btn-sm btn-info">View</a>',
        ];
    }

    public function getStudentListReport($filters = [])
    {
        $query = $this->studentReportRepository->getStudentListQuery($filters);

        return $query->get()->map(function ($student) {
            return $this->formatStudentRow($student);
        });
    }

    public function getGenderWiseData($filters = [])
    {
        return $this->studentReportRepository->getGenderWiseData($filters);
    }

    public function getDirectoryData($filters = [])
    {
        return $this->studentReportRepository->getDirectoryQuery($filters);
    }

    public function formatDirectoryRow($student): array
    {
        $session = $student->sessions->first();

        $fullName = $student->full_name ?: 'Unknown';
        $classSection = $session && $session->classSection
            ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name
            : '';

        $primaryGuardian = $student->guardians->firstWhere('is_primary', true) ?? $student->guardians->first();
        $parentName = $primaryGuardian?->name ?? ($student->parents->first()?->full_name ?? '-');
        $parentMobile = $primaryGuardian?->phone ?? ($student->parents->first()?->phone ?? '-');
        $email = $student->user?->email ?? $primaryGuardian?->email ?? '-';
        $gender = ucfirst($student->gender ?? '-');
        $dob = $student->date_of_birth?->format('d-m-Y') ?? '-';

        $photoUrl = $student->photo_path ? asset('storage/' . $student->photo_path) : null;
        $photoHtml = $photoUrl
            ? '<img src="' . $photoUrl . '" alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit:cover;">'
            : '<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width:40px;height:40px;font-size:14px;font-weight:600;">' . strtoupper(substr($student->first_name ?? '?', 0, 1)) . '</span>';

        $profileUrl = route('students.show', $student->id);

        return [
            'photo' => $photoHtml,
            'admission_no' => $student->admission_no ?? '',
            'student_name' => $fullName,
            'class_section' => $classSection,
            'gender' => $gender,
            'date_of_birth' => $dob,
            'parent_name' => $parentName,
            'parent_mobile' => $parentMobile,
            'email' => $email,
            'status' => $student->trashed() ? 'Inactive' : 'Active',
            'student_id' => $student->id,
            'profile_url' => $profileUrl,
        ];
    }
}