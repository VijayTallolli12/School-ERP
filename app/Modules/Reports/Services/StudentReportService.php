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
}