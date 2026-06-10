<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Modules\Reports\Services\AttendanceReportService;
use App\Modules\Reports\Repositories\AttendanceReportRepository;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Modules\Reports\Exports\AttendanceReportExport;

class AttendanceReportController extends Controller
{
    protected $attendanceReportService;
    protected $attendanceReportRepository;

    public function __construct(
        AttendanceReportService $attendanceReportService,
        AttendanceReportRepository $attendanceReportRepository
    ) {
        $this->attendanceReportService = $attendanceReportService;
        $this->attendanceReportRepository = $attendanceReportRepository;
        $this->middleware('can:attendance.view');
    }

    public function index()
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $todaySummary = $this->attendanceReportService->todaySummary();

        $trendData = $this->attendanceReportService->dailyTrendData([
            'school_id' => $schoolId,
        ]);

        $classWiseData = $this->attendanceReportService->classWiseSummary([
            'date' => Carbon::today()->toDateString(),
            'school_id' => $schoolId,
        ]);

        return view("modules.reports.attendance.index", compact(
            'academicYears', 'classSections', 'todaySummary', 'trendData', 'classWiseData'
        ));
    }

    public function daily(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $filters = $request->only(['academic_year_id', 'class_section_id', 'date']);
        if (!$request->has('date')) {
            $filters['date'] = Carbon::today()->toDateString();
        }
        $filters['school_id'] = $schoolId;

        $summary = $this->attendanceReportService->dailyReport($filters);

        return view("modules.reports.attendance.daily", compact(
            'academicYears',
            'classSections',
            'filters',
            'summary'
        ));
    }

    public function dailyList(Request $request)
    {
        if ($request->ajax()) {
            $schoolId = auth()->user()->school_id;
            $filters = $request->only(['academic_year_id', 'class_section_id', 'date']);
            if (!$request->has('date')) {
                $filters['date'] = Carbon::today()->toDateString();
            }
            $filters['school_id'] = $schoolId;

            $query = $this->attendanceReportRepository->dailyQuery($filters)
                ->select([
                    'attendances.id',
                    'attendances.student_id',
                    'attendances.class_section_id',
                    'attendances.status',
                    'attendances.attendance_date',
                    'attendances.remarks'
                ]);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row) {
                    return $row->student?->full_name ?? 'N/A';
                })
                ->addColumn('class_section_name', function ($row) {
                    return optional($row->classSection)->display_name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'present' => '<span class="badge bg-success">Present</span>',
                        'absent' => '<span class="badge bg-danger">Absent</span>',
                        'late' => '<span class="badge bg-warning text-dark">Late</span>',
                        'leave' => '<span class="badge bg-info">Leave</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }
    }

    public function monthly(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $classSectionId = $request->get('class_section_id');

        $report = [];
        if ($classSectionId) {
            $report = $this->attendanceReportService->monthlyReport($classSectionId, $month, $year);
        }

        return view("modules.reports.attendance.monthly", compact(
            'academicYears',
            'classSections',
            'month',
            'year',
            'classSectionId',
            'report'
        ));
    }

    public function classWise(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $academicYears = AcademicYear::when($schoolId, function ($query, $schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $filters = $request->only(['academic_year_id', 'date']);
        if (!$request->has('date')) {
            $filters['date'] = Carbon::today()->toDateString();
        }
        $filters['school_id'] = $schoolId;

        $report = $this->attendanceReportService->classWiseReport($filters);

        return view("modules.reports.attendance.class_wise", compact(
            'academicYears',
            'classSections',
            'filters',
            'report'
        ));
    }

    // ─── Daily Attendance Exports ─────────────────────────────────

    protected function getDailyExportData(Request $request): array
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'class_section_id', 'date']);
        if (!$request->has('date')) {
            $filters['date'] = Carbon::today()->toDateString();
        }
        $filters['school_id'] = $schoolId;

        return $this->attendanceReportRepository->dailyQuery($filters)
            ->with(['student.user', 'classSection.schoolClass', 'classSection.section'])
            ->get()
            ->map(fn($attendance) => [
                'student_name' => $attendance->student?->full_name ?? 'N/A',
                'class_section' => $attendance->classSection?->display_name ?? 'N/A',
                'status' => ucfirst($attendance->status),
                'attendance_date' => $attendance->attendance_date instanceof \Carbon\Carbon
                    ? $attendance->attendance_date->format('Y-m-d')
                    : $attendance->attendance_date,
                'remarks' => $attendance->remarks ?? '-',
            ])
            ->toArray();
    }

    public function exportDailyPdf(Request $request)
    {
        $data = $this->getDailyExportData($request);
        $title = 'Daily Attendance Report';
        $date = $request->get('date', Carbon::today()->toDateString());

        return Pdf::loadView('modules.reports.attendance.daily_pdf', compact('data', 'title', 'date'))
            ->setPaper('a4', 'landscape')
            ->download("daily_attendance_{$date}_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportDailyExcel(Request $request)
    {
        $data = $this->getDailyExportData($request);
        return Excel::download(new AttendanceReportExport($data, 'daily'), "daily_attendance_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printDaily(Request $request)
    {
        $data = $this->getDailyExportData($request);
        $title = 'Daily Attendance Report';
        $date = $request->get('date', Carbon::today()->toDateString());

        return view('modules.reports.attendance.daily_print', compact('data', 'title', 'date'));
    }

    // ─── Monthly Attendance Exports ───────────────────────────────

    protected function getMonthlyExportData(Request $request): array
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $classSectionId = $request->get('class_section_id');

        if (!$classSectionId) {
            return [];
        }

        $report = $this->attendanceReportService->monthlyReport($classSectionId, $month, $year);
        return $report['student_breakdown'] ?? [];
    }

    public function exportMonthlyPdf(Request $request)
    {
        $data = $this->getMonthlyExportData($request);
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $classSectionId = $request->get('class_section_id');

        $summary = [];
        if ($classSectionId) {
            $report = $this->attendanceReportService->monthlyReport($classSectionId, $month, $year);
            $summary = $report['summary'] ?? [];
        }

        $title = 'Monthly Attendance Report';

        return Pdf::loadView('modules.reports.attendance.monthly_pdf', compact('data', 'title', 'month', 'year', 'summary'))
            ->setPaper('a4', 'landscape')
            ->download("monthly_attendance_{$year}_{$month}_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportMonthlyExcel(Request $request)
    {
        $data = $this->getMonthlyExportData($request);
        return Excel::download(new AttendanceReportExport($data, 'monthly'), "monthly_attendance_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printMonthly(Request $request)
    {
        $data = $this->getMonthlyExportData($request);
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $classSectionId = $request->get('class_section_id');

        $summary = [];
        if ($classSectionId) {
            $report = $this->attendanceReportService->monthlyReport($classSectionId, $month, $year);
            $summary = $report['summary'] ?? [];
        }

        $title = 'Monthly Attendance Report';

        return view('modules.reports.attendance.monthly_print', compact('data', 'title', 'summary'));
    }

    // ─── Class-wise Attendance Exports ────────────────────────────

    protected function getClassWiseExportData(Request $request): array
    {
        $schoolId = auth()->user()->school_id;
        $filters = $request->only(['academic_year_id', 'date']);
        if (!$request->has('date')) {
            $filters['date'] = Carbon::today()->toDateString();
        }
        $filters['school_id'] = $schoolId;

        $report = $this->attendanceReportService->classWiseReport($filters);
        return $report['class_summary'] ?? [];
    }

    public function exportClassWisePdf(Request $request)
    {
        $data = $this->getClassWiseExportData($request);
        $title = 'Class-wise Attendance Report';
        $date = $request->get('date', Carbon::today()->toDateString());

        return Pdf::loadView('modules.reports.attendance.class_wise_pdf', compact('data', 'title', 'date'))
            ->setPaper('a4', 'landscape')
            ->download("class_wise_attendance_{$date}_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportClassWiseExcel(Request $request)
    {
        $data = $this->getClassWiseExportData($request);
        return Excel::download(new AttendanceReportExport($data, 'class_wise'), "class_wise_attendance_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printClassWise(Request $request)
    {
        $data = $this->getClassWiseExportData($request);
        $title = 'Class-wise Attendance Report';
        $date = $request->get('date', Carbon::today()->toDateString());

        $totalPresent = collect($data)->sum('present');
        $totalAbsent = collect($data)->sum('absent');
        $totalLate = collect($data)->sum('late');
        $totalLeave = collect($data)->sum('leave');
        $totalRecords = collect($data)->sum('total');
        $overallPct = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        $overall = compact('totalPresent', 'totalAbsent', 'totalLate', 'totalLeave', 'totalRecords', 'overallPct');

        return view('modules.reports.attendance.class_wise_print', compact('data', 'title', 'overall'));
    }
}
