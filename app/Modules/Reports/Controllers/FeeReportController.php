<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Fees\Services\FeeService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Reports\Exports\FeeReportExport;

class FeeReportController extends Controller
{
    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
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

        return compact('academicYears', 'classSections');
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
}
