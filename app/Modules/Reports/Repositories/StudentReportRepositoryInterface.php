<?php

namespace App\Modules\Reports\Repositories;

interface StudentReportRepositoryInterface
{
    public function getStudentListQuery($filters = []);
    public function getAdmissionReportData($filters = []);
    public function getClassWiseReportData($filters = []);
    public function getDirectoryQuery($filters = []);
    public function getGenderWiseData($filters = []);
}