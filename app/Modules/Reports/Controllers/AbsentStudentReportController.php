<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Reports\Services\AbsentStudentReportService;
use App\Modules\Reports\Exports\AbsentStudentReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AbsentStudentReportController extends Controller
{
    public function __construct(
        protected AbsentStudentReportService $absentStudentReportService
    ) {
        $this->middleware('can:attendance.view');
    }

    protected function getSharedData()
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, fn ($q) => $q->whereHas('schoolClass', fn ($sq) => $sq->where('school_id', $schoolId)))
            ->get();

        return compact('academicYears', 'classSections');
    }

    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $data = $this->getSharedData();

        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $summary = $this->absentStudentReportService->getSummary($filters);
        $classWiseChart = $this->absentStudentReportService->getClassWiseChartData($filters);
        $trendChart = $this->absentStudentReportService->getTrendChartData($filters);

        $students = $this->absentStudentReportService->getStudentsByClass(
            $request->get('class_section_id'),
            $schoolId
        );

        return view('modules.reports.absent_students.index', array_merge($data, compact(
            'filters', 'summary', 'classWiseChart', 'trendChart', 'students'
        )));
    }

    public function data(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $reportData = $this->absentStudentReportService->getReportData($filters);

        return DataTables::of($reportData)
            ->addIndexColumn()
            ->addColumn('consecutive_badge', function ($row) {
                $days = $row['consecutive_days'];
                if ($days >= 3) {
                    return '<span class="badge bg-danger">' . $days . ' days</span>';
                }
                return '<span class="badge bg-warning text-dark">' . $days . ' day' . ($days > 1 ? 's' : '') . '</span>';
            })
            ->rawColumns(['consecutive_badge'])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $data = $this->absentStudentReportService->getReportData($filters);

        return Excel::download(new AbsentStudentReportExport($data), "absent_students_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function exportPdf(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $data = $this->absentStudentReportService->getReportData($filters);
        $summary = $this->absentStudentReportService->getSummary($filters);
        $title = 'Absent Students Report';
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        return Pdf::loadView('modules.reports.absent_students.pdf', compact('data', 'summary', 'title', 'fromDate', 'toDate'))
            ->setPaper('a4', 'landscape')
            ->download("absent_students_" . now()->format('Ymd_His') . ".pdf");
    }

    public function printReport(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $data = $this->absentStudentReportService->getReportData($filters);
        $summary = $this->absentStudentReportService->getSummary($filters);
        $title = 'Absent Students Report';
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        return view('modules.reports.absent_students.print', compact('data', 'summary', 'title', 'fromDate', 'toDate'));
    }

    public function classWiseChartData(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $data = $this->absentStudentReportService->getClassWiseChartData($filters);
        return response()->json($data);
    }

    public function trendChartData(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'student_id', 'from_date', 'to_date']);
        $filters['from_date'] = $filters['from_date'] ?? Carbon::today()->toDateString();
        $filters['to_date'] = $filters['to_date'] ?? Carbon::today()->toDateString();
        $filters['school_id'] = $schoolId;

        $data = $this->absentStudentReportService->getTrendChartData($filters);
        return response()->json($data);
    }

    public function getStudentsByClass(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $classSectionId = $request->get('class_section_id');
        $students = $this->absentStudentReportService->getStudentsByClass($classSectionId, $schoolId);
        return response()->json($students);
    }
}
