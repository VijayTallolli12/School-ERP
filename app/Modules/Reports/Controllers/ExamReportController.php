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

    public function topPerformers(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->reportService->topPerformers(
                $request->get('academic_year_id'),
                $request->get('exam_id'),
                $request->get('class_section_id'),
                $request->get('subject_id'),
                (int) ($request->get('top_n', 10))
            );
            return DataTables::of($data['ranked'])
                ->addIndexColumn()
                ->make(true);
        }

        $shared = $this->getSharedData();

        // Fetch exams with subject+class label for dropdown
        $schoolId = auth()->user()->school_id ?? null;
        $examsWithLabel = Exam::with(['subject', 'classSection.schoolClass', 'classSection.section'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'label' => $e->exam_name . ' (' . ($e->subject?->name ?? 'N/A') . ') - ' . ($e->classSection?->display_name ?? 'N/A'),
            ]);

        return view("Reports::exams.top_performers", array_merge($shared, [
            'examsWithLabel' => $examsWithLabel,
        ]));
    }

    public function getTopPerformersData(Request $request): array
    {
        return $this->reportService->topPerformers(
            $request->get('academic_year_id'),
            $request->get('exam_id'),
            $request->get('class_section_id'),
            $request->get('subject_id'),
            (int) ($request->get('top_n', 10))
        );
    }

    public function passFailAnalysis(Request $request)
    {
        $shared = $this->getSharedData();
        $data = $this->reportService->passFailAnalysis(
            $request->get('academic_year_id'),
            $request->get('exam_id'),
            $request->get('class_section_id'),
            $request->get('subject_id'),
            $request->get('from_date'),
            $request->get('to_date')
        );

        $schoolId = auth()->user()->school_id ?? null;
        $examsWithLabel = Exam::with(['subject', 'classSection.schoolClass', 'classSection.section'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'label' => $e->exam_name . ' (' . ($e->subject?->name ?? 'N/A') . ') - ' . ($e->classSection?->display_name ?? 'N/A'),
            ]);

        return view("Reports::exams.pass_fail_analysis", array_merge($shared, [
            'analysis' => $data,
            'examsWithLabel' => $examsWithLabel,
            'filters' => $request->only(['academic_year_id', 'exam_id', 'class_section_id', 'subject_id', 'from_date', 'to_date']),
        ]));
    }

    public function getPassFailData(Request $request): array
    {
        return $this->reportService->passFailAnalysis(
            $request->get('academic_year_id'),
            $request->get('exam_id'),
            $request->get('class_section_id'),
            $request->get('subject_id'),
            $request->get('from_date'),
            $request->get('to_date')
        );
    }

    public function exportPassFailPdf(Request $request)
    {
        $data = $this->getPassFailData($request);
        $title = 'Pass/Fail Analysis Report';

        return Pdf::loadView('Reports::exams.pass_fail_analysis_pdf', compact('data', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("pass_fail_analysis_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportPassFailExcel(Request $request)
    {
        $data = $this->getPassFailData($request);
        return Excel::download(new ExamReportExport($data['studentAnalysis'], 'pass_fail_analysis'), "pass_fail_analysis_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printPassFail(Request $request)
    {
        $data = $this->getPassFailData($request);
        $title = 'Pass/Fail Analysis Report';

        return view('Reports::exams.pass_fail_analysis_print', compact('data', 'title'));
    }

    public function exportTopPerformersPdf(Request $request)
    {
        $data = $this->getTopPerformersData($request);
        $title = 'Top Performers Report';
        $topN = (int) ($request->get('top_n', 10));

        return Pdf::loadView('Reports::exams.top_performers_pdf', compact('data', 'title', 'topN'))
            ->setPaper('a4', 'landscape')
            ->download("top_performers_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportTopPerformersExcel(Request $request)
    {
        $data = $this->getTopPerformersData($request);
        return Excel::download(new ExamReportExport($data['ranked'], 'top_performers'), "top_performers_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printTopPerformers(Request $request)
    {
        $data = $this->getTopPerformersData($request);
        $title = 'Top Performers Report';
        $topN = (int) ($request->get('top_n', 10));

        return view('Reports::exams.top_performers_print', compact('data', 'title', 'topN'));
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
