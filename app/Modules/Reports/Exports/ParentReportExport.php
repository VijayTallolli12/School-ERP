<?php

namespace App\Modules\Reports\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParentReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $type;

    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function array(): array
    {
        if ($this->type === 'list') {
            return array_map(fn ($row) => [
                'parent_name' => $row['parent_name'] ?? '',
                'email' => $row['email'] ?? '',
                'phone' => $row['phone'] ?? '',
                'occupation' => $row['occupation'] ?? '',
                'status' => $row['status'] ?? '',
                'linked_students' => $row['linked_students'] ?? 0,
                'classes' => $row['classes'] ?? '',
            ], $this->data);
        }

        if ($this->type === 'mapping') {
            return array_map(fn ($row) => [
                'parent_name' => $row['parent_name'] ?? '',
                'parent_email' => $row['parent_email'] ?? '',
                'parent_phone' => $row['parent_phone'] ?? '',
                'student_name' => $row['student_name'] ?? '',
                'admission_no' => $row['admission_no'] ?? '',
                'class_section' => $row['class_section'] ?? '',
                'relationship' => $row['relationship'] ?? '',
                'is_primary' => ! empty($row['is_primary']) ? 'Yes' : 'No',
            ], $this->data);
        }

        if ($this->type === 'activity_summary') {
            return array_map(fn ($row) => [
                'parent_name' => $row['parent_name'] ?? '',
                'email' => $row['email'] ?? '',
                'phone' => $row['phone'] ?? '',
                'linked_students' => $row['linked_students'] ?? 0,
                'notifications_count' => $row['notifications_count'] ?? 0,
                'attendance_access' => $row['attendance_access'] ?? 0,
                'fees_access' => $row['fees_access'] ?? 0,
                'exam_access' => $row['exam_access'] ?? 0,
            ], $this->data);
        }

        return array_map(function ($row) {
            $flat = [];
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $flat[$key] = json_encode($value);
                } else {
                    $flat[$key] = $value;
                }
            }
            return $flat;
        }, $this->data);
    }

    public function headings(): array
    {
        if ($this->type === 'list') {
            return ['Parent Name', 'Email', 'Phone', 'Occupation', 'Status', 'Linked Students', 'Classes'];
        }

        if ($this->type === 'mapping') {
            return ['Parent Name', 'Parent Email', 'Parent Phone', 'Student Name', 'Admission No', 'Class/Section', 'Relationship', 'Primary Contact'];
        }

        if ($this->type === 'activity_summary') {
            return ['Parent Name', 'Email', 'Phone', 'Linked Students', 'Notifications', 'Attendance Access', 'Fees Access', 'Exam Access'];
        }

        if (empty($this->data)) {
            return [];
        }

        $firstRow = reset($this->data);
        $headings = array_keys((array) $firstRow);

        return array_map(function ($heading) {
            return ucwords(str_replace('_', ' ', $heading));
        }, $headings);
    }

    public function title(): string
    {
        return ucfirst(str_replace('_', ' ', $this->type)) . ' Report';
    }
}
