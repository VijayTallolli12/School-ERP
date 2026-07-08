<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Fees\Models\FeeStructure;
use App\Modules\Students\Models\Student;
use Yajra\DataTables\Facades\DataTables;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Reports\Exports\FeeReportExport;
use App\Modules\Reports\Repositories\FeeDefaulterReportRepositoryInterface;

class FeeReportController extends Controller
{
    protected $feeService;
    protected $defaulterRepo;

    public function __construct(FeeService $feeService, FeeDefaulterReportRepositoryInterface $defaulterRepo)
    {
        $this->feeService = $feeService;
        $this->defaulterRepo = $defaulterRepo;
        $this->middleware('can:fees.reports');
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

        $feeStructures = FeeStructure::with(['classSection.schoolClass', 'classSection.section'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get()
            ->map(fn($fs) => [
                'id' => $fs->id,
                'label' => ($fs->classSection?->display_name ?? 'N/A') . ' - ' . ($fs->name ?? 'Default'),
            ]);

        return compact('academicYears', 'classSections', 'feeStructures');
    }

    public function index()
    {
        $data = $this->getSharedData();
        $data['stats'] = $this->feeService->dashboardFeeStats();
        return view("Reports::fees.index", $data);
    }

    public function collectionSummary(Request $request)
    {
        if ($request->ajax()) {
            $academicYearId = $request->get('academic_year_id') ?: null;
            $data = $academicYearId ? $this->feeService->classWiseFeeReport($academicYearId) : [];
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::fees.collection_summary", $this->getSharedData());
    }

    public function paid(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->feeService->collectionReport(
                $request->get('from_date'),
                $request->get('to_date'),
                $request->get('class_section_id'),
                $request->get('payment_mode')
            );
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        
        return view("Reports::fees.paid", $this->getSharedData());
    }

    public function pending(Request $request)
    {
        if ($request->ajax()) {
            $academicYearId = $request->get('academic_year_id');
            $data = $this->feeService->dueReport($academicYearId, false);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
        
        return view("Reports::fees.pending", $this->getSharedData());
    }

    public function overdue(Request $request)
    {
        if ($request->ajax()) {
            $academicYearId = $request->get('academic_year_id');
            $data = $this->feeService->dueReport($academicYearId, true);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::fees.overdue", $this->getSharedData());
    }

    protected function getReportData($type, Request $request)
    {
        switch ($type) {
            case 'paid':
                return $this->feeService->collectionReport(
                    $request->get('from_date'),
                    $request->get('to_date'),
                    $request->get('class_section_id'),
                    $request->get('payment_mode')
                );
            case 'pending':
                return $this->feeService->dueReport($request->get('academic_year_id'), false);
            case 'overdue':
                return $this->feeService->dueReport($request->get('academic_year_id'), true);
            case 'collection_summary':
                $academicYearId = $request->get('academic_year_id') ?: (AcademicYear::where('status', 'active')->value('id'));
                return $academicYearId ? $this->feeService->classWiseFeeReport($academicYearId) : [];
            default:
                return [];
        }
    }

    public function exportPdf($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Fees Report';
        
        return Pdf::loadView('Reports::fees.pdf', compact('data', 'type', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("{$type}_fees_report_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportExcel($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        return Excel::download(new FeeReportExport($data, $type), "{$type}_fees_report_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printReport($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Fees Report';
        
        return view('Reports::fees.print', compact('data', 'type', 'title'));
    }

    // --- Fee Defaulters Report ---

    public function defaulters(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->defaulterRepo->defaulters(
                $request->get('academic_year_id'),
                $request->get('class_section_id'),
                $request->get('student_id'),
                $request->get('fee_structure_id'),
                $request->get('from_due_date'),
                $request->get('to_due_date'),
                $request->get('min_outstanding') !== null ? (float) $request->get('min_outstanding') : null,
                $request->get('max_outstanding') !== null ? (float) $request->get('max_outstanding') : null
            );

            return response()->json($data);
        }

        $shared = $this->getSharedData();
        return view("Reports::fees.defaulters", $shared);
    }

    public function getStudentsByClass(Request $request)
    {
        $classSectionId = $request->get('class_section_id');
        $students = $this->defaulterRepo->getStudentsByClass($classSectionId);

        return response()->json($students->map(fn($s) => [
            'id' => $s->id,
            'text' => $s->full_name . ' (' . $s->admission_no . ')',
        ]));
    }

    public function exportDefaultersPdf(Request $request)
    {
        $data = $this->defaulterRepo->defaulters(
            $request->get('academic_year_id'),
            $request->get('class_section_id'),
            $request->get('student_id'),
            $request->get('fee_structure_id'),
            $request->get('from_due_date'),
            $request->get('to_due_date'),
            $request->get('min_outstanding') !== null ? (float) $request->get('min_outstanding') : null,
            $request->get('max_outstanding') !== null ? (float) $request->get('max_outstanding') : null
        );
        $title = 'Fee Defaulters Report';

        return Pdf::loadView('Reports::fees.defaulters_pdf', compact('data', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("fee_defaulters_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportDefaultersExcel(Request $request)
    {
        $data = $this->defaulterRepo->defaulters(
            $request->get('academic_year_id'),
            $request->get('class_section_id'),
            $request->get('student_id'),
            $request->get('fee_structure_id'),
            $request->get('from_due_date'),
            $request->get('to_due_date'),
            $request->get('min_outstanding') !== null ? (float) $request->get('min_outstanding') : null,
            $request->get('max_outstanding') !== null ? (float) $request->get('max_outstanding') : null
        );

        return Excel::download(new FeeReportExport($data['defaulters'], 'fee_defaulters'), "fee_defaulters_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printDefaulters(Request $request)
    {
        $data = $this->defaulterRepo->defaulters(
            $request->get('academic_year_id'),
            $request->get('class_section_id'),
            $request->get('student_id'),
            $request->get('fee_structure_id'),
            $request->get('from_due_date'),
            $request->get('to_due_date'),
            $request->get('min_outstanding') !== null ? (float) $request->get('min_outstanding') : null,
            $request->get('max_outstanding') !== null ? (float) $request->get('max_outstanding') : null
        );
        $title = 'Fee Defaulters Report';

        return view('Reports::fees.defaulters_print', compact('data', 'title'));
    }
}
