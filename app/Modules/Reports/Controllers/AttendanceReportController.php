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

        return view("modules.reports.attendance.index", compact('academicYears', 'classSections', 'todaySummary'));
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
                    'attendances.status',
                    'attendances.attendance_date',
                    'attendances.remarks'
                ]);

            return DataTables::of($query)
                ->addColumn('DT_RowIndex', function () {
                    static $count = 0;
                    return ++$count;
                })
                ->addColumn('student_name', function ($row) {
                    return optional($row->student->user)->full_name ?? optional($row->student)->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'present' => '<span class="badge badge-success">Present</span>',
                        'absent' => '<span class="badge badge-danger">Absent</span>',
                        'late' => '<span class="badge badge-warning">Late</span>',
                        'leave' => '<span class="badge badge-info">Leave</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
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
}
