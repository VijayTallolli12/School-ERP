<?php

namespace App\Modules\Payroll\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Exports\PayrollReportExport;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Repositories\PayrollRepositoryInterface;
use App\Modules\Payroll\Requests\StorePayrollDepartmentRequest;
use App\Modules\Payroll\Requests\StorePayrollDesignationRequest;
use App\Modules\Payroll\Requests\StoreSalaryComponentRequest;
use App\Modules\Payroll\Requests\StorePayGradeRequest;
use App\Modules\Payroll\Requests\StoreEmployeeSalaryStructureRequest;
use App\Modules\Payroll\Requests\UpdatePayrollDepartmentRequest;
use App\Modules\Payroll\Requests\UpdatePayrollDesignationRequest;
use App\Modules\Payroll\Requests\UpdateSalaryComponentRequest;
use App\Modules\Payroll\Requests\UpdatePayGradeRequest;
use App\Modules\Payroll\Requests\UpdateEmployeeSalaryStructureRequest;
use App\Modules\Payroll\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollRepositoryInterface $payroll,
        private readonly PayrollService $service,
    ) {
    }

    public function index()
    {
        return view('modules.payroll.index', [
            'departments' => PayrollDepartment::query()->orderBy('name')->get(),
            'designations' => PayrollDesignation::query()->with('department')->orderBy('name')->get(),
            'salaryComponents' => SalaryComponent::query()->orderBy('name')->get(),
            'payGrades' => PayGrade::query()->orderBy('name')->get(),
            'salaryStructures' => EmployeeSalaryStructure::query()->with('payGrade')->latest()->get(),
        ]);
    }

    // ─── Departments ─────────────────────────────────────────────────────

    public function departmentsData(): JsonResponse
    {
        return DataTables::of($this->payroll->departments())
            ->editColumn('status', fn (PayrollDepartment $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->addColumn('actions', fn (PayrollDepartment $d) => view('modules.payroll._actions', ['type' => 'department', 'model' => $d])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeDepartment(StorePayrollDepartmentRequest $request): JsonResponse
    {
        return $this->jsonCreated('Payroll department created successfully.', $this->service->createDepartment($request->validated()));
    }

    public function showDepartment(PayrollDepartment $department): JsonResponse
    {
        return $this->jsonData($department);
    }

    public function updateDepartment(UpdatePayrollDepartmentRequest $request, PayrollDepartment $department): JsonResponse
    {
        return $this->jsonCreated('Payroll department updated successfully.', $this->service->updateDepartment($department, $request->validated()));
    }

    public function destroyDepartment(PayrollDepartment $department): JsonResponse
    {
        $this->authorize('delete', $department);
        $department->delete();

        return $this->jsonMessage('Payroll department deleted successfully.');
    }

    // ─── Designations ────────────────────────────────────────────────────

    public function designationsData(): JsonResponse
    {
        return DataTables::of($this->payroll->designations())
            ->addColumn('department_name', fn (PayrollDesignation $d) => $d->department?->name ?? '-')
            ->editColumn('status', fn (PayrollDesignation $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->addColumn('actions', fn (PayrollDesignation $d) => view('modules.payroll._actions', ['type' => 'designation', 'model' => $d])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeDesignation(StorePayrollDesignationRequest $request): JsonResponse
    {
        return $this->jsonCreated('Payroll designation created successfully.', $this->service->createDesignation($request->validated()));
    }

    public function showDesignation(PayrollDesignation $designation): JsonResponse
    {
        return $this->jsonData($designation->load('department'));
    }

    public function updateDesignation(UpdatePayrollDesignationRequest $request, PayrollDesignation $designation): JsonResponse
    {
        return $this->jsonCreated('Payroll designation updated successfully.', $this->service->updateDesignation($designation, $request->validated()));
    }

    public function destroyDesignation(PayrollDesignation $designation): JsonResponse
    {
        $this->authorize('delete', $designation);
        $designation->delete();

        return $this->jsonMessage('Payroll designation deleted successfully.');
    }

    // ─── Salary Components ───────────────────────────────────────────────

    public function salaryComponentsData(): JsonResponse
    {
        return DataTables::of($this->payroll->salaryComponents())
            ->editColumn('component_type', fn (SalaryComponent $c) => '<span class="badge bg-'.($c->component_type === 'earning' ? 'success' : 'danger').'">'.$c->component_type.'</span>')
            ->editColumn('calculation_type', fn (SalaryComponent $c) => '<span class="badge bg-info">'.$c->calculation_type.'</span>')
            ->editColumn('value', fn (SalaryComponent $c) => '<span class="text-end d-block">'.number_format((float) $c->value, 2).'</span>')
            ->editColumn('status', fn (SalaryComponent $c) => '<span class="badge bg-'.($c->status === 'active' ? 'success' : 'secondary').'">'.$c->status.'</span>')
            ->addColumn('actions', fn (SalaryComponent $c) => view('modules.payroll._actions', ['type' => 'salary-component', 'model' => $c])->render())
            ->rawColumns(['component_type', 'calculation_type', 'value', 'status', 'actions'])
            ->toJson();
    }

    public function storeSalaryComponent(StoreSalaryComponentRequest $request): JsonResponse
    {
        return $this->jsonCreated('Salary component created successfully.', $this->service->createSalaryComponent($request->validated()));
    }

    public function showSalaryComponent(SalaryComponent $salaryComponent): JsonResponse
    {
        return $this->jsonData($salaryComponent);
    }

    public function updateSalaryComponent(UpdateSalaryComponentRequest $request, SalaryComponent $salaryComponent): JsonResponse
    {
        return $this->jsonCreated('Salary component updated successfully.', $this->service->updateSalaryComponent($salaryComponent, $request->validated()));
    }

    public function destroySalaryComponent(SalaryComponent $salaryComponent): JsonResponse
    {
        $this->authorize('delete', $salaryComponent);
        $salaryComponent->delete();

        return $this->jsonMessage('Salary component deleted successfully.');
    }

    // ─── Pay Grades ──────────────────────────────────────────────────────

    public function payGradesData(): JsonResponse
    {
        return DataTables::of($this->payroll->payGrades())
            ->editColumn('min_salary', fn (PayGrade $g) => $g->min_salary !== null ? number_format((float) $g->min_salary, 2) : '-')
            ->editColumn('max_salary', fn (PayGrade $g) => $g->max_salary !== null ? number_format((float) $g->max_salary, 2) : '-')
            ->editColumn('status', fn (PayGrade $g) => '<span class="badge bg-'.($g->status === 'active' ? 'success' : 'secondary').'">'.$g->status.'</span>')
            ->addColumn('actions', fn (PayGrade $g) => view('modules.payroll._actions', ['type' => 'pay-grade', 'model' => $g])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storePayGrade(StorePayGradeRequest $request): JsonResponse
    {
        return $this->jsonCreated('Pay grade created successfully.', $this->service->createPayGrade($request->validated()));
    }

    public function showPayGrade(PayGrade $payGrade): JsonResponse
    {
        return $this->jsonData($payGrade);
    }

    public function updatePayGrade(UpdatePayGradeRequest $request, PayGrade $payGrade): JsonResponse
    {
        return $this->jsonCreated('Pay grade updated successfully.', $this->service->updatePayGrade($payGrade, $request->validated()));
    }

    public function destroyPayGrade(PayGrade $payGrade): JsonResponse
    {
        $this->authorize('delete', $payGrade);
        $payGrade->delete();

        return $this->jsonMessage('Pay grade deleted successfully.');
    }

    // ─── Salary Structures ───────────────────────────────────────────────

    public function salaryStructuresData(): JsonResponse
    {
        return DataTables::of($this->payroll->salaryStructures())
            ->addColumn('pay_grade_name', fn (EmployeeSalaryStructure $s) => $s->payGrade?->name ?? '-')
            ->addColumn('employee_name', fn (EmployeeSalaryStructure $s) => $s->employee?->full_name ?? '-')
            ->editColumn('total_ctc', fn (EmployeeSalaryStructure $s) => '<span class="text-end d-block">'.number_format((float) $s->total_ctc, 2).'</span>')
            ->editColumn('effective_from', fn (EmployeeSalaryStructure $s) => $s->effective_from?->format('d M Y') ?? '-')
            ->editColumn('effective_to', fn (EmployeeSalaryStructure $s) => $s->effective_to?->format('d M Y') ?? '-')
            ->editColumn('status', fn (EmployeeSalaryStructure $s) => '<span class="badge bg-'.($s->status === 'active' ? 'success' : 'secondary').'">'.$s->status.'</span>')
            ->addColumn('actions', fn (EmployeeSalaryStructure $s) => view('modules.payroll._actions', ['type' => 'salary-structure', 'model' => $s])->render())
            ->rawColumns(['total_ctc', 'status', 'actions'])
            ->toJson();
    }

    public function storeSalaryStructure(StoreEmployeeSalaryStructureRequest $request): JsonResponse
    {
        return $this->jsonCreated('Employee salary structure created successfully.', $this->service->createSalaryStructure($request->validated()));
    }

    public function showSalaryStructure(EmployeeSalaryStructure $salaryStructure): JsonResponse
    {
        return $this->jsonData($salaryStructure->load(['payGrade', 'employee']));
    }

    public function updateSalaryStructure(UpdateEmployeeSalaryStructureRequest $request, EmployeeSalaryStructure $salaryStructure): JsonResponse
    {
        return $this->jsonCreated('Employee salary structure updated successfully.', $this->service->updateSalaryStructure($salaryStructure, $request->validated()));
    }

    public function destroySalaryStructure(EmployeeSalaryStructure $salaryStructure): JsonResponse
    {
        $this->authorize('delete', $salaryStructure);
        $salaryStructure->delete();

        return $this->jsonMessage('Employee salary structure deleted successfully.');
    }

    // ─── Reports ─────────────────────────────────────────────────────────

    public function reports()
    {
        return view('modules.payroll.reports', [
            'payGrades' => PayGrade::query()->orderBy('name')->get(),
            'salaryComponents' => SalaryComponent::query()->orderBy('name')->get(),
            'departments' => PayrollDepartment::query()->orderBy('name')->get(),
            'designations' => PayrollDesignation::query()->orderBy('name')->get(),
            'salaryStructures' => EmployeeSalaryStructure::query()->with(['payGrade', 'employee'])->latest()->get(),
        ]);
    }

    public function departmentsReportData(Request $request): JsonResponse
    {
        $query = PayrollDepartment::query()->withCount('designations');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('status', fn (PayrollDepartment $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function designationsReportData(Request $request): JsonResponse
    {
        $query = PayrollDesignation::query()->with('department');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('department_name', fn (PayrollDesignation $d) => $d->department?->name ?? '-')
            ->editColumn('status', fn (PayrollDesignation $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function salaryComponentsReportData(Request $request): JsonResponse
    {
        $query = SalaryComponent::query();

        if ($request->filled('component_type')) {
            $query->where('component_type', $request->component_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('component_type', fn (SalaryComponent $c) => '<span class="badge bg-'.($c->component_type === 'earning' ? 'success' : 'danger').'">'.$c->component_type.'</span>')
            ->editColumn('calculation_type', fn (SalaryComponent $c) => '<span class="badge bg-info">'.$c->calculation_type.'</span>')
            ->editColumn('value', fn (SalaryComponent $c) => '<span class="text-end d-block">'.number_format((float) $c->value, 2).'</span>')
            ->editColumn('status', fn (SalaryComponent $c) => '<span class="badge bg-'.($c->status === 'active' ? 'success' : 'secondary').'">'.$c->status.'</span>')
            ->rawColumns(['component_type', 'calculation_type', 'value', 'status'])
            ->toJson();
    }

    public function payGradesReportData(Request $request): JsonResponse
    {
        $query = PayGrade::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('min_salary', fn (PayGrade $g) => $g->min_salary !== null ? number_format((float) $g->min_salary, 2) : '-')
            ->editColumn('max_salary', fn (PayGrade $g) => $g->max_salary !== null ? number_format((float) $g->max_salary, 2) : '-')
            ->editColumn('status', fn (PayGrade $g) => '<span class="badge bg-'.($g->status === 'active' ? 'success' : 'secondary').'">'.$g->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    public function salaryStructuresReportData(Request $request): JsonResponse
    {
        $query = EmployeeSalaryStructure::query()->with(['payGrade', 'employee']);

        if ($request->filled('pay_grade_id')) {
            $query->where('pay_grade_id', $request->pay_grade_id);
        }
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('pay_grade_name', fn (EmployeeSalaryStructure $s) => $s->payGrade?->name ?? '-')
            ->addColumn('employee_name', fn (EmployeeSalaryStructure $s) => $s->employee?->full_name ?? '-')
            ->editColumn('total_ctc', fn (EmployeeSalaryStructure $s) => '<span class="text-end d-block">'.number_format((float) $s->total_ctc, 2).'</span>')
            ->editColumn('effective_from', fn (EmployeeSalaryStructure $s) => $s->effective_from?->format('d M Y') ?? '-')
            ->editColumn('effective_to', fn (EmployeeSalaryStructure $s) => $s->effective_to?->format('d M Y') ?? '-')
            ->editColumn('status', fn (EmployeeSalaryStructure $s) => '<span class="badge bg-'.($s->status === 'active' ? 'success' : 'secondary').'">'.$s->status.'</span>')
            ->rawColumns(['total_ctc', 'status'])
            ->toJson();
    }

    public function employeeListReportData(Request $request): JsonResponse
    {
        $query = EmployeeSalaryStructure::query()->with(['payGrade', 'employee']);

        if ($request->filled('pay_grade_id')) {
            $query->where('pay_grade_id', $request->pay_grade_id);
        }
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('pay_grade_name', fn (EmployeeSalaryStructure $s) => $s->payGrade?->name ?? '-')
            ->addColumn('employee_name', fn (EmployeeSalaryStructure $s) => $s->employee?->full_name ?? '-')
            ->editColumn('employee_type', fn (EmployeeSalaryStructure $s) => class_basename($s->employee_type))
            ->editColumn('total_ctc', fn (EmployeeSalaryStructure $s) => '<span class="text-end d-block">'.number_format((float) $s->total_ctc, 2).'</span>')
            ->editColumn('effective_from', fn (EmployeeSalaryStructure $s) => $s->effective_from?->format('d M Y') ?? '-')
            ->editColumn('effective_to', fn (EmployeeSalaryStructure $s) => $s->effective_to?->format('d M Y') ?? '-')
            ->editColumn('status', fn (EmployeeSalaryStructure $s) => '<span class="badge bg-'.($s->status === 'active' ? 'success' : 'secondary').'">'.$s->status.'</span>')
            ->rawColumns(['total_ctc', 'status'])
            ->toJson();
    }

    // ─── Exports ─────────────────────────────────────────────────────────

    public function exportExcel(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);

        return Excel::download(
            new PayrollReportExport($data, $report),
            "payroll_{$report}_".now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportPdf(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return Pdf::loadView('modules.payroll.reports_pdf', compact('data', 'title', 'report'))
            ->setPaper('a4', 'landscape')
            ->download("payroll_{$report}_".now()->format('Ymd_His').'.pdf');
    }

    public function printReport(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return view('modules.payroll.reports_print', compact('data', 'title', 'report'));
    }

    private function getReportData(Request $request, string $report): array
    {
        return match ($report) {
            'departments' => PayrollDepartment::query()->withCount('designations')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayrollDepartment $d) => [
                    'name' => $d->name,
                    'description' => $d->description ?? '-',
                    'sort_order' => $d->sort_order,
                    'designations_count' => $d->designations_count,
                    'status' => $d->status,
                ])->toArray(),

            'designations' => PayrollDesignation::query()->with('department')
                ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->department_id))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayrollDesignation $d) => [
                    'name' => $d->name,
                    'department' => $d->department?->name ?? '-',
                    'description' => $d->description ?? '-',
                    'status' => $d->status,
                ])->toArray(),

            'salary_components' => SalaryComponent::query()
                ->when($request->filled('component_type'), fn ($q) => $q->where('component_type', $request->component_type))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (SalaryComponent $c) => [
                    'name' => $c->name,
                    'name_display' => $c->name_display,
                    'component_type' => $c->component_type,
                    'calculation_type' => $c->calculation_type,
                    'value' => number_format((float) $c->value, 2),
                    'sort_order' => $c->sort_order,
                    'status' => $c->status,
                ])->toArray(),

            'pay_grades' => PayGrade::query()
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayGrade $g) => [
                    'name' => $g->name,
                    'description' => $g->description ?? '-',
                    'min_salary' => $g->min_salary !== null ? number_format((float) $g->min_salary, 2) : '-',
                    'max_salary' => $g->max_salary !== null ? number_format((float) $g->max_salary, 2) : '-',
                    'status' => $g->status,
                ])->toArray(),

            'salary_structures' => EmployeeSalaryStructure::query()->with(['payGrade', 'employee'])
                ->when($request->filled('pay_grade_id'), fn ($q) => $q->where('pay_grade_id', $request->pay_grade_id))
                ->when($request->filled('employee_type'), fn ($q) => $q->where('employee_type', $request->employee_type))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (EmployeeSalaryStructure $s) => [
                    'employee' => $s->employee?->full_name ?? '-',
                    'employee_type' => class_basename($s->employee_type),
                    'pay_grade' => $s->payGrade?->name ?? '-',
                    'effective_from' => $s->effective_from?->format('d M Y') ?? '-',
                    'effective_to' => $s->effective_to?->format('d M Y') ?? '-',
                    'total_ctc' => number_format((float) $s->total_ctc, 2),
                    'status' => $s->status,
                ])->toArray(),

            'employee_list' => EmployeeSalaryStructure::query()->with(['payGrade', 'employee'])
                ->when($request->filled('pay_grade_id'), fn ($q) => $q->where('pay_grade_id', $request->pay_grade_id))
                ->when($request->filled('employee_type'), fn ($q) => $q->where('employee_type', $request->employee_type))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (EmployeeSalaryStructure $s) => [
                    'employee' => $s->employee?->full_name ?? '-',
                    'employee_type' => class_basename($s->employee_type),
                    'pay_grade' => $s->payGrade?->name ?? '-',
                    'effective_from' => $s->effective_from?->format('d M Y') ?? '-',
                    'effective_to' => $s->effective_to?->format('d M Y') ?? '-',
                    'total_ctc' => number_format((float) $s->total_ctc, 2),
                    'status' => $s->status,
                ])->toArray(),

            default => [],
        };
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function jsonCreated(string $message, mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function jsonData(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function jsonMessage(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
