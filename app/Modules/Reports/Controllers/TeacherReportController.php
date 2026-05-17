<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Reports\Services\TeacherReportService;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\ClassSection;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Reports\Exports\TeacherReportExport;

class TeacherReportController extends Controller
{
    protected $reportService;

    public function __construct(TeacherReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('can:teachers.reports'); // Assume this permission is used for teacher reports
    }

    protected function getSharedData()
    {
        $schoolId = auth()->user()->school_id ?? null;

        $subjects = Subject::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $teachers = Teacher::query()
            ->where('school_id', $schoolId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'employee_id']);

        $teacherStatuses = Teacher::statuses();
        $attendanceStatuses = TeacherAttendance::statuses();

        return compact('subjects', 'classSections', 'teachers', 'teacherStatuses', 'attendanceStatuses');
    }

    public function index()
    {
        $stats = $this->reportService->dashboardStats();
        return view("Reports::teachers.index", compact('stats'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'subject_id', 'class_section_id', 'joining_date_from', 'joining_date_to']);
            $data = $this->reportService->teacherList($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::teachers.list", $this->getSharedData());
    }

    public function attendance(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['teacher_id', 'status', 'attendance_status', 'month', 'year', 'from_date', 'to_date']);
            $data = $this->reportService->attendance($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::teachers.attendance", $this->getSharedData());
    }

    public function subjectAllocation(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'subject_id']);
            $data = $this->reportService->subjectAllocation($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::teachers.subject_allocation", $this->getSharedData());
    }

    public function classTeacherMapping(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'class_section_id']);
            $data = $this->reportService->classTeacherMapping($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::teachers.class_teacher_mapping", $this->getSharedData());
    }

    protected function getReportData($type, Request $request)
    {
        switch ($type) {
            case 'list':
                return $this->reportService->teacherList($request->only(['status', 'subject_id', 'class_section_id', 'joining_date_from', 'joining_date_to']));
            case 'attendance':
                return $this->reportService->attendance($request->only(['teacher_id', 'status', 'attendance_status', 'month', 'year', 'from_date', 'to_date']));
            case 'subject_allocation':
                return $this->reportService->subjectAllocation($request->only(['status', 'subject_id']));
            case 'class_teacher_mapping':
                return $this->reportService->classTeacherMapping($request->only(['status', 'class_section_id']));
            default:
                return [];
        }
    }

    public function exportPdf($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Report';
        
        return Pdf::loadView('Reports::teachers.pdf', compact('data', 'type', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("teacher_{$type}_report_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportExcel($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        return Excel::download(new TeacherReportExport($data, $type), "teacher_{$type}_report_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printReport($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Report';
        
        return view('Reports::teachers.print', compact('data', 'type', 'title'));
    }
}
