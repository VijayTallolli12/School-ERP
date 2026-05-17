<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Reports\Services\ParentReportService;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Parents\Models\Guardian;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Reports\Exports\ParentReportExport;

class ParentReportController extends Controller
{
    protected $reportService;

    public function __construct(ParentReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('can:parents.reports');
    }

    protected function getSharedData()
    {
        $schoolId = auth()->user()->school_id ?? null;

        $classSections = ClassSection::with(['schoolClass', 'section'])
            ->when($schoolId, function ($query, $schoolId) {
                $query->whereHas('schoolClass', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            })->get();

        $parentStatuses = Guardian::statuses();

        return compact('classSections', 'parentStatuses');
    }

    public function index()
    {
        $stats = $this->reportService->dashboardStats();
        return view("Reports::parents.index", compact('stats'));
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'occupation', 'class_section_id']);
            $data = $this->reportService->parentList($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::parents.list", $this->getSharedData());
    }

    public function mapping(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'class_section_id']);
            $data = $this->reportService->mapping($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::parents.mapping", $this->getSharedData());
    }

    public function activitySummary(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['status', 'from_date', 'to_date']);
            $data = $this->reportService->activitySummary($filters);
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }

        return view("Reports::parents.activity_summary", $this->getSharedData());
    }

    protected function getReportData($type, Request $request)
    {
        switch ($type) {
            case 'list':
                return $this->reportService->parentList($request->only(['status', 'occupation', 'class_section_id']));
            case 'mapping':
                return $this->reportService->mapping($request->only(['status', 'class_section_id']));
            case 'activity_summary':
                return $this->reportService->activitySummary($request->only(['status', 'from_date', 'to_date']));
            default:
                return [];
        }
    }

    public function exportPdf($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Report';
        
        return Pdf::loadView('Reports::parents.pdf', compact('data', 'type', 'title'))
            ->setPaper('a4', 'landscape')
            ->download("parent_{$type}_report_" . now()->format('Ymd_His') . ".pdf");
    }

    public function exportExcel($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        return Excel::download(new ParentReportExport($data, $type), "parent_{$type}_report_" . now()->format('Ymd_His') . ".xlsx");
    }

    public function printReport($type, Request $request)
    {
        $data = $this->getReportData($type, $request);
        $title = ucfirst(str_replace('_', ' ', $type)) . ' Report';
        
        return view('Reports::parents.print', compact('data', 'type', 'title'));
    }
}
