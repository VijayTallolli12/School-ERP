<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Modules\Reports\Exports\StudentListExport;
use App\Modules\Reports\Exports\StudentDirectoryExport;
use App\Modules\Reports\Exports\GenderWiseExport;
use App\Modules\Reports\Exports\AdmissionReportExport;
use App\Modules\Reports\Exports\ClassWiseReportExport;
use App\Modules\Reports\Services\StudentReportService;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentReportController extends Controller
{
    protected $studentReportService;

    public function __construct(StudentReportService $studentReportService)
    {
        $this->studentReportService = $studentReportService;
        $this->middleware('can:students.view');
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

        return view("modules.reports.students.index", compact('academicYears', 'classSections'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['academic_year_id', 'class_section_id', 'status']);
            $query = $this->studentReportService->getStudentListData($filters);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('full_name', function ($student) {
                    return $this->studentReportService->formatStudentRow($student)->full_name;
                })
                ->addColumn('class_section', function ($student) {
                    return $this->studentReportService->formatStudentRow($student)->class_section;
                })
                ->addColumn('guardian', function ($student) {
                    return $this->studentReportService->formatStudentRow($student)->guardian;
                })
                ->addColumn('actions', function ($student) {
                    return $this->studentReportService->formatStudentRow($student)->actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    public function admission(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $academicYears = AcademicYear::where('school_id', auth()->user()->school_id)->get();
        $data = $this->studentReportService->getAdmissionReportData($filters);
        $totalAdmissions = $data->sum('total_admissions');

        return view("modules.reports.students.admission", compact('data', 'totalAdmissions', 'academicYears'));
    }

    public function classWise(Request $request)
    {
        $filters = $request->only(['academic_year_id']);
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

        $data = $this->studentReportService->getClassWiseReportData($filters);

        return view("modules.reports.students.class_wise", compact('data', 'academicYears', 'classSections'));
    }

    public function directory(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['academic_year_id', 'class_section_id', 'status', 'gender', 'start_date', 'end_date']);
            $query = $this->studentReportService->getDirectoryData($filters);

            // Cache per student ID to avoid calling formatDirectoryRow N times per row
            $formatCache = [];

            $format = function ($student) use (&$formatCache) {
                $id = $student->id;
                if (!isset($formatCache[$id])) {
                    $formatCache[$id] = $this->studentReportService->formatDirectoryRow($student);
                }
                return $formatCache[$id];
            };

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('photo', fn($s) => $format($s)['photo'])
                ->addColumn('student_name', fn($s) => $format($s)['student_name'])
                ->addColumn('class_section', fn($s) => $format($s)['class_section'])
                ->addColumn('gender', fn($s) => $format($s)['gender'])
                ->addColumn('date_of_birth', fn($s) => $format($s)['date_of_birth'])
                ->addColumn('parent_name', fn($s) => $format($s)['parent_name'])
                ->addColumn('parent_mobile', fn($s) => $format($s)['parent_mobile'])
                ->addColumn('email', fn($s) => $format($s)['email'])
                ->addColumn('status_badge', fn($s) => $format($s)['status'] === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('actions', function ($s) use ($format) {
                    $row = $format($s);
                    $contactJs = "window.location.href='tel:" . e($row['parent_mobile']) . "'";
                    return '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="' . e($row['profile_url']) . '"><i class="ti ti-eye me-2"></i>View Profile</a></li>
                            <li><a class="dropdown-item" href="#" onclick="' . $contactJs . '"><i class="ti ti-phone me-2"></i>Call Parent</a></li>
                        </ul></div>';
                })
                ->rawColumns(['photo', 'status_badge', 'actions'])
                ->make(true);
        }

        $schoolId = auth()->user()->school_id;
        $academicYears = AcademicYear::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->get();
        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, fn($q) => $q->whereHas('schoolClass', fn($sq) => $sq->where('school_id', $schoolId)))
            ->get();

        return view("modules.reports.students.directory", compact('academicYears', 'classSections'));
    }

    public function genderWise(Request $request)
    {
        $filters = $request->only(['academic_year_id', 'class_section_id', 'start_date', 'end_date']);
        $data = $this->studentReportService->getGenderWiseData($filters);

        $rows = $data->map(function ($item) {
            $total = (int) $item->total;
            $male = (int) $item->male;
            $female = (int) $item->female;
            $other = (int) $item->other;
            return [
                'class_name' => $item->class_name,
                'total' => $total,
                'male' => $male,
                'female' => $female,
                'other' => $other,
                'male_pct' => $total > 0 ? round(($male / $total) * 100, 1) : 0,
                'female_pct' => $total > 0 ? round(($female / $total) * 100, 1) : 0,
            ];
        })->toArray();

        $totals = [
            'total' => array_sum(array_column($rows, 'total')),
            'male' => array_sum(array_column($rows, 'male')),
            'female' => array_sum(array_column($rows, 'female')),
            'other' => array_sum(array_column($rows, 'other')),
        ];

        $schoolId = auth()->user()->school_id;
        $academicYears = AcademicYear::when($schoolId, fn($q) => $q->where('school_id', $schoolId))->get();
        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, fn($q) => $q->whereHas('schoolClass', fn($sq) => $sq->where('school_id', $schoolId)))
            ->get();

        return view("modules.reports.students.gender_wise", compact('rows', 'totals', 'academicYears', 'classSections'));
    }

    public function exportGenderWise(Request $request, $type)
    {
        $filters = $request->only(['academic_year_id', 'class_section_id', 'start_date', 'end_date']);
        $data = $this->studentReportService->getGenderWiseData($filters);

        $rows = $data->map(function ($item) {
            $total = (int) $item->total;
            $male = (int) $item->male;
            $female = (int) $item->female;
            $other = (int) $item->other;
            return [
                'class_name' => $item->class_name,
                'total' => $total,
                'male' => $male,
                'female' => $female,
                'other' => $other,
                'male_pct' => $total > 0 ? round(($male / $total) * 100, 1) : 0,
                'female_pct' => $total > 0 ? round(($female / $total) * 100, 1) : 0,
            ];
        })->toArray();

        $totals = [
            'total' => array_sum(array_column($rows, 'total')),
            'male' => array_sum(array_column($rows, 'male')),
            'female' => array_sum(array_column($rows, 'female')),
            'other' => array_sum(array_column($rows, 'other')),
        ];

        if ($type === 'excel') {
            return Excel::download(new GenderWiseExport($rows, $totals), 'gender_wise_report.xlsx');
        } elseif ($type === 'pdf') {
            $title = 'Gender-wise Student Report';
            $pdf = Pdf::loadView('modules.reports.students.exports.gender_wise_pdf', compact('rows', 'totals', 'title'));
            return $pdf->setPaper('a4', 'landscape')->download('gender_wise_report.pdf');
        } elseif ($type === 'print') {
            $title = 'Gender-wise Student Report';
            return view('modules.reports.students.exports.gender_wise_print', compact('rows', 'totals', 'title'));
        }
    }

    public function exportDirectory(Request $request, $type)
    {
        $filters = $request->only(['academic_year_id', 'class_section_id', 'status', 'gender', 'start_date', 'end_date']);
        $students = $this->studentReportService->getDirectoryData($filters)->get();
        $rows = $students->map(fn($s) => $this->studentReportService->formatDirectoryRow($s))->toArray();

        if ($type === 'excel') {
            return Excel::download(new StudentDirectoryExport($rows), 'student_directory.xlsx');
        } elseif ($type === 'pdf') {
            $title = 'Student Directory Report';
            $pdf = Pdf::loadView('modules.reports.students.exports.directory_pdf', compact('rows', 'title'));
            return $pdf->setPaper('a4', 'landscape')->download('student_directory.pdf');
        } elseif ($type === 'print') {
            $title = 'Student Directory Report';
            return view('modules.reports.students.exports.directory_print', compact('rows', 'title'));
        }
    }

    public function exportList(Request $request, $type)
    {
        $filters = $request->only(['academic_year_id', 'class_section_id', 'status']);
        $data = $this->studentReportService->getStudentListReport($filters);

        if ($type === 'excel') {
            return Excel::download(new StudentListExport($data), 'student_list.xlsx');
        } elseif ($type === 'pdf') {
            $pdf = Pdf::loadView('modules.reports.students.exports.list_pdf', compact('data'));
            return $pdf->download('student_list.pdf');
        } elseif ($type === 'print') {
            return view('modules.reports.students.exports.list_print', compact('data'));
        }
    }

    public function exportAdmission(Request $request, $type)
    {
        $filters = $request->only(['start_date', 'end_date']);
        $data = $this->studentReportService->getAdmissionReportData($filters);
        $totalAdmissions = $data->sum('total_admissions');

        if ($type === 'excel') {
            return Excel::download(new AdmissionReportExport($data, $totalAdmissions), 'admission_report.xlsx');
        } elseif ($type === 'pdf') {
            $pdf = Pdf::loadView('modules.reports.students.exports.admission_pdf', compact('data', 'totalAdmissions'));
            return $pdf->download('admission_report.pdf');
        } elseif ($type === 'print') {
            return view('modules.reports.students.exports.admission_print', compact('data', 'totalAdmissions'));
        }
    }

    public function exportClassWise(Request $request, $type)
    {
        $filters = $request->only(['academic_year_id']);
        $data = $this->studentReportService->getClassWiseReportData($filters);

        if ($type === 'excel') {
            return Excel::download(new ClassWiseReportExport($data), 'class_wise_report.xlsx');
        } elseif ($type === 'pdf') {
            $pdf = Pdf::loadView('modules.reports.students.exports.class_wise_pdf', compact('data'));
            return $pdf->download('class_wise_report.pdf');
        } elseif ($type === 'print') {
            return view('modules.reports.students.exports.class_wise_print', compact('data'));
        }
    }
}
