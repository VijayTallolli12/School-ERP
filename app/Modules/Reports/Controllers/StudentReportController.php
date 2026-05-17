<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Modules\Reports\Exports\StudentListExport;
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
                    $name = trim(optional($student->user)->first_name . ' ' . optional($student->user)->last_name);
                    return $name ?: ($student->full_name ?: 'Unknown Student');
                })
                ->addColumn('class_section', function ($student) {
                    $session = $student->sessions->first();
                    return $session && $session->classSection
                        ? $session->classSection->schoolClass->name . ' - ' . $session->classSection->section->name
                        : '';
                })
                ->addColumn('guardian', function ($student) {
                    return $student->guardians->map(function ($guardian) {
                        return optional($guardian->user)->first_name ?: $guardian->name;
                    })->filter()->join(', ');
                })
                ->addColumn('actions', function ($student) {
                    return '<a href="#" class="btn btn-sm btn-info">View</a>';
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

    public function exportList(Request $request, $type)
    {
        $filters = $request->only(['academic_year_id', 'class_section_id', 'status']);
        $query = $this->studentReportService->getStudentListData($filters);
        $data = $query->get();

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
