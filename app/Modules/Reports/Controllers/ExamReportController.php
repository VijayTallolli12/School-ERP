<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Reports\Services\ExamReportService;
use App\Modules\Academics\Services\ClassesService;
use App\Models\AcademicYear;
use App\Modules\Exams\Models\Exam;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use App\Modules\Students\Models\Student;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Reports\Exports\ExamReportExport;

class ExamReportController extends Controller
{
    protected $reportService;
    protected $classService;

    public function __construct(ExamReportService $reportService, ClassesService $classService)
    {
        $this->reportService = $reportService;
        $this->classService = $classService;
        $this->middleware('can:exams.reports'); // if needed, let's assume 'exams.reports' or just auth is handled in route
    }

    protected function getSharedData()
    {
        $schoolId = auth()->user()->school_id ?? null;

        $academicYears = AcademicYear::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $subjects = Subject::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();
        
        $exams = Exam::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->select('id', 'exam_name')->distinct()->get();

        return compact('academicYears', 'classSections', 'subjects', 'exams');
    }

    public function index()
    {
        $stats = $this->reportService->dashboardStats();
        return view("Reports::exams.index", compact('stats'));
    }

    public function results(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->reportService->examResults(
                $request->get('academic_year_id'),
                $request->get('exam_id'),
                $request->get('class_section_id'),
                $request->get('subject_id')
            );
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::exams.results", $this->getSharedData());
    }

    public function classPerformance(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->reportService->classPerformance(
                $request->get('academic_year_id'),
                $request->get('exam_id')
            );
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::exams.class_performance", $this->getSharedData());
    }

    public function subjectPerformance(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->reportService->subjectPerformance(
                $request->get('academic_year_id'),
                $request->get('exam_id'),
                $request->get('class_section_id')
            );
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::exams.subject_performance", $this->getSharedData());
    }

    public function studentSummary(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->reportService->studentSummary(
                $request->get('student_id'),
                $request->get('academic_year_id')
            );
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        $students = Student::where('school_id', auth()->user()->school_id ?? null)->select('id', 'first_name', 'last_name', 'admission_no')->get();
        $data = $this->getSharedData();
        $data['students'] = $students;

        return view("Reports::exams.student_summary", $data);
    }

    protected function getReportData($type, Request $request)
    {
        switch ($type) {
            case 'results':
                return $this->reportService->examResults(
                    $request->get('academic_year_id'),
                    $request->get('exam_id'),
                    $request->get('class_section_id'),
                    $request->get('subject_id')
                );
            case 'class_performance':
                return $this->reportService->classPerformance(
                    $request->get('academic_year_id'),
                    $request->get('exam_id')
                );
            case 'subject_performance':
                return $this->reportService->subjectPerformance(
                    $request->get('academic_year_id'),
                    $request->get('exam_id'),
                    $request->get('class_section_id')
                );
            case 'student_summary':
                return $this->reportService->studentSummary(
                    $request->get('student_id'),
                    $request->get('academic_year_id')
                );
            default:
                return [];
        }
    }

    public function exportPdf($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Exam Report';
        
        return Pdf::loadView('Reports::exams.pdf', compact('data', 'type', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("{$type}_exam_report_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportExcel($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        return Excel::download(new ExamReportExport($data, $type), "{$type}_exam_report_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printReport($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Exam Report';
        
        return view('Reports::exams.print', compact('data', 'type', 'title'));
    }
}
