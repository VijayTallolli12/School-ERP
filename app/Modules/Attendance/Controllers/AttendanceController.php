<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Attendance\Exports\AttendanceExport;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Modules\Attendance\Requests\AttendanceReportFilterRequest;
use App\Modules\Attendance\Requests\BulkMarkAttendanceRequest;
use App\Modules\Attendance\Requests\MarkAttendanceRequest;
use App\Modules\Attendance\Requests\UpdateAttendanceRequest;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Students\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class AttendanceController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private readonly AttendanceRepositoryInterface $attendances,
        private readonly AttendanceService $service,
    ) {}

    public function index(): View
    {
        return view('modules.attendance.index', [
            'academicYears' => AcademicYear::query()->where('status', 'active')->orderByDesc('starts_on')->get(),
            'classSections' => ClassSection::query()
                ->with(['schoolClass', 'section'])
                ->where('status', 'active')
                ->get()
                ->sortBy(fn (ClassSection $cs) => $cs->schoolClass->sort_order.'-'.$cs->section->name),
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    public function data(): JsonResponse
    {
        $query = $this->attendances->filterQuery($this->attendances->query(), $this->listFiltersFromRequest());

        return DataTables::eloquent($query)
            ->addColumn('student_name', fn (Attendance $attendance) => e($attendance->student->full_name ?? '-'))
            ->addColumn('roll_no', function (Attendance $attendance): string {
                $session = $attendance->student->sessions
                    ->firstWhere('class_section_id', $attendance->class_section_id);

                return $session?->roll_no ?? '-';
            })
            ->addColumn('class_section', fn (Attendance $attendance) => e($attendance->classSection->schoolClass->name.' - '.$attendance->classSection->section->name))
            ->addColumn('attendance_date', fn (Attendance $attendance) => $attendance->attendance_date->format('d-M-Y'))
            ->addColumn('status', function (Attendance $attendance): string {
                $statusClass = match ($attendance->status) {
                    'present' => 'success',
                    'absent' => 'danger',
                    'late' => 'warning',
                    'half_day' => 'info',
                    'excused' => 'secondary',
                    default => 'dark',
                };

                return '<span class="badge bg-'.$statusClass.'">'.e($attendance->status_label).'</span>';
            })
            ->addColumn('marked_by', fn (Attendance $attendance) => e($attendance->markedBy?->name ?? '-'))
            ->addColumn('remarks', fn (Attendance $attendance) => $attendance->remarks ? '<small>'.e($attendance->remarks).'</small>' : '-')
            ->addColumn('actions', fn (Attendance $attendance) => view('modules.attendance._actions', compact('attendance'))->render())
            ->filterColumn('student_name', function ($q, $keyword): void {
                $kw = '%'.addcslashes($keyword, '%_\\').'%';
                $q->whereHas('student', function ($sq) use ($kw): void {
                    $sq->where(function ($inner) use ($kw): void {
                        $inner->where('first_name', 'like', $kw)
                            ->orWhere('middle_name', 'like', $kw)
                            ->orWhere('last_name', 'like', $kw);
                    });
                });
            })
            ->filterColumn('class_section', function ($q, $keyword): void {
                $kw = '%'.addcslashes($keyword, '%_\\').'%';
                $q->where(function ($w) use ($kw): void {
                    $w->whereHas('classSection.schoolClass', fn ($sq) => $sq->where('name', 'like', $kw))
                        ->orWhereHas('classSection.section', fn ($sq) => $sq->where('name', 'like', $kw));
                });
            })
            ->rawColumns(['status', 'remarks', 'actions'])
            ->toJson();
    }

    public function statistics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getStatistics($this->listFiltersFromRequest()),
        ]);
    }

    public function store(MarkAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        $attendance = $this->service->markAttendance($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully.',
            'data' => $attendance,
        ]);
    }

    public function show(Attendance $attendance): JsonResponse
    {
        $this->authorize('view', $attendance);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'class_section_id' => $attendance->class_section_id,
                'academic_year_id' => $attendance->academic_year_id,
                'attendance_date' => $attendance->attendance_date->toDateString(),
                'status' => $attendance->status,
                'remarks' => $attendance->remarks,
            ],
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);
        $attendance = $this->service->update($attendance, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully.',
            'data' => $attendance,
        ]);
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);
        $this->service->delete($attendance);

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully.',
        ]);
    }

    public function bulkMark(BulkMarkAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        $results = $this->service->bulkMarkAttendance($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked for '.count($results).' students.',
            'data' => $results,
        ]);
    }

    public function getStudentsByClassSection(ClassSection $classSection): JsonResponse
    {
        $this->authorize('viewForAttendance', $classSection);

        $students = Student::query()
            ->whereHas('sessions', function ($query) use ($classSection) {
                $query->where('class_section_id', $classSection->id)
                    ->where('status', 'active');
            })
            ->with([
                'sessions' => function ($query) use ($classSection) {
                    $query->where('class_section_id', $classSection->id);
                },
            ])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json([
            'success' => true,
            'data' => $students->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->full_name,
                'roll_no' => $student->sessions->first()?->roll_no ?? '-',
            ]),
        ]);
    }

    public function monthlyReport(ClassSection $classSection): JsonResponse
    {
        $this->authorize('viewForAttendance', $classSection);

        $classSection->loadMissing(['schoolClass', 'section']);

        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);

        $report = $this->service->getMonthlyReportDetail($classSection->id, $month, $year);

        return response()->json([
            'success' => true,
            'data' => [
                'class_section' => $classSection->schoolClass->name.' - '.$classSection->section->name,
                'month' => $month,
                'year' => $year,
                'summary' => $report['summary'],
                'students' => $report['students'],
            ],
        ]);
    }

    public function exportExcel(AttendanceReportFilterRequest $request): BinaryFileResponse
    {
        $filters = $request->filterPayload();

        return Excel::download(
            new AttendanceExport($filters),
            'attendance-'.now()->format('Y-m-d-His').'.xlsx'
        );
    }

    public function exportPdf(AttendanceReportFilterRequest $request)
    {
        $filters = $request->filterPayload();
        $rows = $this->attendances->filterQuery($this->attendances->query(), $filters)
            ->orderByDesc('attendance_date')
            ->limit(5000)
            ->get();

        return Pdf::loadView('modules.attendance.report_pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'title' => 'Attendance Report',
        ])
            ->setPaper('a4', 'landscape')
            ->download('attendance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function printReport(AttendanceReportFilterRequest $request): View
    {
        $filters = $request->filterPayload();
        $rows = $this->attendances->filterQuery($this->attendances->query(), $filters)
            ->orderByDesc('attendance_date')
            ->limit(5000)
            ->get();

        return view('modules.attendance.print', [
            'rows' => $rows,
            'filters' => $filters,
            'title' => 'Attendance Report',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function listFiltersFromRequest(): array
    {
        return array_filter([
            'class_section_id' => request('filters.class_section_id'),
            'from_date' => request('filters.from_date'),
            'to_date' => request('filters.to_date'),
            'status' => request('filters.status'),
            'academic_year_id' => request('filters.academic_year_id'),
        ], fn ($v) => $v !== null && $v !== '');
    }
}
