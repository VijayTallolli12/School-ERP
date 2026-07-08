<?php

namespace App\Modules\Payroll\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Exports\PayrollReportExport;
use App\Modules\Payroll\Models\PayrollDepartment;
use App\Modules\Payroll\Models\PayrollDesignation;
use App\Modules\Payroll\Models\SalaryComponent;
use App\Modules\Payroll\Models\PayGrade;
use App\Modules\Payroll\Models\EmployeeSalaryStructure;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollItem;
use App\Modules\Payroll\Models\EmployeePayslip;
use App\Modules\Payroll\Repositories\PayrollRepositoryInterface;
use App\Modules\Payroll\Requests\StorePayrollDepartmentRequest;
use App\Modules\Payroll\Requests\StorePayrollDesignationRequest;
use App\Modules\Payroll\Requests\StoreSalaryComponentRequest;
use App\Modules\Payroll\Requests\StorePayGradeRequest;
use App\Modules\Payroll\Requests\StoreEmployeeSalaryStructureRequest;
use App\Modules\Payroll\Requests\GeneratePayslipRequest;
use App\Modules\Payroll\Requests\BulkGeneratePayslipRequest;
use App\Modules\Payroll\Requests\UpdatePayrollDepartmentRequest;
use App\Modules\Payroll\Requests\UpdatePayrollDesignationRequest;
use App\Modules\Payroll\Requests\UpdateSalaryComponentRequest;
use App\Modules\Payroll\Requests\UpdatePayGradeRequest;
use App\Modules\Payroll\Requests\UpdateEmployeeSalaryStructureRequest;
use App\Modules\Payroll\Requests\GeneratePayrollRequest;
use App\Modules\Payroll\Requests\LockPayrollRunRequest;
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
        if (auth()->user()->hasRole('Teacher')) {
            $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first();
            if (! $teacher) {
                abort(403, 'Teacher profile not found.');
            }
            return view('modules.payroll.teacher_payslips', [
                'teacher' => $teacher,
            ]);
        }

        return view('modules.payroll.index', [
            'departments' => PayrollDepartment::query()->orderBy('name')->get(),
            'designations' => PayrollDesignation::query()->with('department')->orderBy('name')->get(),
            'salaryComponents' => SalaryComponent::query()->orderBy('name')->get(),
            'payGrades' => PayGrade::query()->orderBy('name')->get(),
            'salaryStructures' => EmployeeSalaryStructure::query()->with('payGrade')->latest()->get(),
            'payrollRuns' => PayrollRun::query()->latest()->get(),
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

    // ─── Payroll Runs ─────────────────────────────────────────────────────

    public function payrollRunsData(): JsonResponse
    {
        return DataTables::of($this->payroll->payrollRuns())
            ->addColumn('period', fn (PayrollRun $r) => $r->month_name.' '.$r->year)
            ->editColumn('status', fn (PayrollRun $r) => '<span class="badge bg-'.($r->status === 'draft' ? 'warning' : 'success').'">'.$r->status.'</span>')
            ->editColumn('generated_at', fn (PayrollRun $r) => $r->generated_at?->format('d M Y H:i') ?? '-')
            ->addColumn('items_count', fn (PayrollRun $r) => $r->items_count)
            ->addColumn('actions', fn (PayrollRun $r) => view('modules.payroll._run_actions', ['run' => $r])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function generatePayroll(GeneratePayrollRequest $request): JsonResponse
    {
        $run = $this->service->generatePayroll($request->month, $request->year, $request->notes);

        return $this->jsonCreated('Payroll generated successfully.', $run);
    }

    public function showPayrollRun(PayrollRun $payrollRun): JsonResponse
    {
        $payrollRun->loadCount('items');

        return $this->jsonData($payrollRun);
    }

    public function lockPayrollRun(LockPayrollRunRequest $request, PayrollRun $payrollRun): JsonResponse
    {
        try {
            $run = $this->service->lockRun($payrollRun, $request->notes);

            return $this->jsonCreated('Payroll run locked successfully.', $run);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroyPayrollRun(PayrollRun $payrollRun): JsonResponse
    {
        $this->authorize('delete', $payrollRun);
        $payrollRun->items()->delete();
        $payrollRun->delete();

        return $this->jsonMessage('Payroll run deleted successfully.');
    }

    public function payRunItemsData(int $runId): JsonResponse
    {
        $run = PayrollRun::query()->findOrFail($runId);
        $query = PayrollItem::query()->where('payroll_run_id', $runId);

        return DataTables::of($query)
            ->addColumn('employee_name', fn (PayrollItem $i) => $i->employee?->full_name ?? $i->employee_id)
            ->editColumn('gross_salary', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->gross_salary, 2).'</span>')
            ->editColumn('total_deductions', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->total_deductions, 2).'</span>')
            ->editColumn('net_salary', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->net_salary, 2).'</span>')
            ->editColumn('status', fn (PayrollItem $i) => '<span class="badge bg-'.($i->status === 'active' ? 'success' : 'secondary').'">'.$i->status.'</span>')
            ->rawColumns(['gross_salary', 'total_deductions', 'net_salary', 'status'])
            ->toJson();
    }

    // ─── Payslips ────────────────────────────────────────────────────────

    public function payslipsData(Request $request): JsonResponse
    {
        $runId = $request->input('payroll_run_id');
        $query = $this->payroll->employeePayslips($runId);

        return DataTables::of($query)
            ->addColumn('employee_name', fn (EmployeePayslip $p) => $p->employee_name)
            ->addColumn('period', fn (EmployeePayslip $p) => $p->payrollRun?->month_name.' '.$p->payrollRun?->year)
            ->editColumn('gross_salary', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->gross_salary, 2).'</span>')
            ->editColumn('total_deductions', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->total_deductions, 2).'</span>')
            ->editColumn('net_salary', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->net_salary, 2).'</span>')
            ->editColumn('generated_at', fn (EmployeePayslip $p) => $p->generated_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', fn (EmployeePayslip $p) => '
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="'.route('admin.payroll.payslips.print', $p->id).'" target="_blank" title="View"><i class="ti ti-eye"></i></a>
                    <a class="btn btn-outline-danger" href="'.route('admin.payroll.payslips.pdf', $p->id).'" title="PDF"><i class="ti ti-file-pdf"></i></a>
                    <a class="btn btn-outline-secondary" href="'.route('admin.payroll.payslips.print', $p->id).'" target="_blank" title="Print"><i class="ti ti-printer"></i></a>
                </div>')
            ->rawColumns(['gross_salary', 'total_deductions', 'net_salary', 'actions'])
            ->toJson();
    }

    public function payslipHistoryData(Request $request): JsonResponse
    {
        $query = $this->payroll->payslipHistory();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                  ->orWhere('payslip_number', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('payslip_number', fn (EmployeePayslip $p) => '<a href="'.route('admin.payroll.payslips.show', $p->id).'" class="view-payslip" data-id="'.$p->id.'">'.$p->payslip_number.'</a>')
            ->addColumn('employee_name', fn (EmployeePayslip $p) => $p->employee_name)
            ->addColumn('period', fn (EmployeePayslip $p) => $p->payrollRun?->month_name.' '.$p->payrollRun?->year)
            ->editColumn('gross_salary', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->gross_salary, 2).'</span>')
            ->editColumn('total_deductions', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->total_deductions, 2).'</span>')
            ->editColumn('net_salary', fn (EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->net_salary, 2).'</span>')
            ->editColumn('generated_at', fn (EmployeePayslip $p) => $p->generated_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', fn (EmployeePayslip $p) => '
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="'.route('admin.payroll.payslips.show', $p->id).'" title="View"><i class="ti ti-eye"></i></a>
                    <a class="btn btn-outline-danger" href="'.route('admin.payroll.payslips.pdf', $p->id).'" title="PDF"><i class="ti ti-file-pdf"></i></a>
                    <a class="btn btn-outline-secondary" href="'.route('admin.payroll.payslips.print', $p->id).'" target="_blank" title="Print"><i class="ti ti-printer"></i></a>
                </div>')
            ->rawColumns(['payslip_number', 'gross_salary', 'total_deductions', 'net_salary', 'actions'])
            ->toJson();
    }

    public function generatePayslip(GeneratePayslipRequest $request): JsonResponse
    {
        try {
            $payslip = $this->service->generatePayslipItem(
                $request->input('payroll_run_id'),
                $request->input('payroll_item_id')
            );
            return $this->jsonCreated('Payslip generated successfully.', $payslip);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function bulkGeneratePayslips(BulkGeneratePayslipRequest $request): JsonResponse
    {
        try {
            $payslips = $this->service->bulkGeneratePayslips($request->input('payroll_run_id'));
            $count = count($payslips);
            return $this->jsonCreated("{$count} payslip(s) generated successfully.", $payslips);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function showPayslip(EmployeePayslip $payslip): JsonResponse
    {
        $payslip->load(['payrollRun', 'payrollItem']);

        return $this->jsonData($payslip);
    }

    public function downloadPayslipPdf(EmployeePayslip $payslip): \Illuminate\Http\Response
    {
        $data = $this->service->getPayslipData($payslip->id);

        return Pdf::loadView('modules.payroll.payslip_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download("payslip_{$payslip->payslip_number}.pdf");
    }

    public function printPayslip(EmployeePayslip $payslip): \Illuminate\View\View
    {
        $data = $this->service->getPayslipData($payslip->id);

        return view('modules.payroll.payslip_print', $data);
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
            'payrollRuns' => PayrollRun::query()->latest()->get(),
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

    // ─── Processing Reports ──────────────────────────────────────────────

    public function runSummaryReportData(Request $request): JsonResponse
    {
        $query = PayrollRun::query()->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('period', fn (PayrollRun $r) => $r->month_name.' '.$r->year)
            ->editColumn('status', fn (PayrollRun $r) => '<span class="badge bg-'.($r->status === 'draft' ? 'warning' : 'success').'">'.$r->status.'</span>')
            ->editColumn('generated_at', fn (PayrollRun $r) => $r->generated_at?->format('d M Y H:i') ?? '-')
            ->addColumn('items_count', fn (PayrollRun $r) => $r->items_count)
            ->rawColumns(['status'])
            ->toJson();
    }

    public function employeePayrollReportData(Request $request): JsonResponse
    {
        $query = PayrollItem::query()->with(['payrollRun']);

        if ($request->filled('payroll_run_id')) {
            $query->where('payroll_run_id', $request->payroll_run_id);
        }
        if ($request->filled('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('period', fn (PayrollItem $i) => $i->payrollRun?->month_name.' '.$i->payrollRun?->year)
            ->addColumn('employee_name', fn (PayrollItem $i) => $i->employee?->full_name ?? $i->employee_id)
            ->editColumn('gross_salary', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->gross_salary, 2).'</span>')
            ->editColumn('total_deductions', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->total_deductions, 2).'</span>')
            ->editColumn('net_salary', fn (PayrollItem $i) => '<span class="text-end d-block">'.number_format((float) $i->net_salary, 2).'</span>')
            ->editColumn('status', fn (PayrollItem $i) => '<span class="badge bg-'.($i->status === 'active' ? 'success' : 'secondary').'">'.$i->status.'</span>')
            ->rawColumns(['gross_salary', 'total_deductions', 'net_salary', 'status'])
            ->toJson();
    }

    public function grossVsNetReportData(Request $request): JsonResponse
    {
        $query = PayrollRun::query()->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('period', fn (PayrollRun $r) => $r->month_name.' '.$r->year)
            ->addColumn('total_gross', fn (PayrollRun $r) => '<span class="text-end d-block">'.number_format((float) $r->items()->sum('gross_salary'), 2).'</span>')
            ->addColumn('total_deductions', fn (PayrollRun $r) => '<span class="text-end d-block">'.number_format((float) $r->items()->sum('total_deductions'), 2).'</span>')
            ->addColumn('total_net', fn (PayrollRun $r) => '<span class="text-end d-block">'.number_format((float) $r->items()->sum('net_salary'), 2).'</span>')
            ->addColumn('items_count', fn (PayrollRun $r) => $r->items_count)
            ->editColumn('status', fn (PayrollRun $r) => '<span class="badge bg-'.($r->status === 'draft' ? 'warning' : 'success').'">'.$r->status.'</span>')
            ->rawColumns(['total_gross', 'total_deductions', 'total_net', 'status'])
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

            'run_summary' => PayrollRun::query()->withCount('items')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayrollRun $r) => [
                    'period' => $r->month_name.' '.$r->year,
                    'status' => $r->status,
                    'generated_by' => $r->generator?->name ?? '-',
                    'generated_at' => $r->generated_at?->format('d M Y H:i') ?? '-',
                    'employees' => $r->items_count,
                    'notes' => $r->notes ?? '-',
                ])->toArray(),

            'employee_payroll' => PayrollItem::query()->with(['payrollRun', 'employee'])
                ->when($request->filled('payroll_run_id'), fn ($q) => $q->where('payroll_run_id', $request->payroll_run_id))
                ->when($request->filled('employee_type'), fn ($q) => $q->where('employee_type', $request->employee_type))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayrollItem $i) => [
                    'period' => $i->payrollRun?->month_name.' '.$i->payrollRun?->year,
                    'employee' => $i->employee?->full_name ?? $i->employee_id,
                    'employee_type' => $i->employee_type,
                    'gross' => number_format((float) $i->gross_salary, 2),
                    'deductions' => number_format((float) $i->total_deductions, 2),
                    'net' => number_format((float) $i->net_salary, 2),
                    'status' => $i->status,
                ])->toArray(),

            'gross_vs_net' => PayrollRun::query()->withCount('items')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (PayrollRun $r) => [
                    'period' => $r->month_name.' '.$r->year,
                    'total_gross' => number_format((float) $r->items()->sum('gross_salary'), 2),
                    'total_deductions' => number_format((float) $r->items()->sum('total_deductions'), 2),
                    'total_net' => number_format((float) $r->items()->sum('net_salary'), 2),
                    'employees' => $r->items_count,
                    'status' => $r->status,
                ])->toArray(),

            default => [],
        };
    }

    // ─── Teacher Payslips ────────────────────────────────────────────────

    public function myPayslips(): \Illuminate\View\View
    {
        return view('modules.payroll.my_payslips', [
            'teacher' => \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->first(),
        ]);
    }

    public function myPayslipsData(): JsonResponse
    {
        $teacher = \App\Modules\Teachers\Models\Teacher::where('user_id', auth()->id())->firstOrFail();

        $query = \App\Modules\Payroll\Models\EmployeePayslip::query()
            ->where('employee_type', 'teacher')
            ->where('employee_id', $teacher->id);

        return DataTables::of($query)
            ->addColumn('period', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => $p->payrollRun?->month_name.' '.$p->payrollRun?->year)
            ->editColumn('gross_salary', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->gross_salary, 2).'</span>')
            ->editColumn('total_deductions', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->total_deductions, 2).'</span>')
            ->editColumn('net_salary', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => '<span class="text-end d-block">'.number_format((float) $p->net_salary, 2).'</span>')
            ->editColumn('generated_at', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => $p->generated_at?->format('d M Y H:i') ?? '-')
            ->addColumn('actions', fn (\App\Modules\Payroll\Models\EmployeePayslip $p) => '
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-primary" href="'.route('admin.payroll.payslips.print', $p->id).'" target="_blank" title="View"><i class="ti ti-eye"></i></a>
                    <a class="btn btn-outline-danger" href="'.route('admin.payroll.payslips.pdf', $p->id).'" title="PDF"><i class="ti ti-file-pdf"></i></a>
                    <a class="btn btn-outline-secondary" href="'.route('admin.payroll.payslips.print', $p->id).'" target="_blank" title="Print"><i class="ti ti-printer"></i></a>
                </div>')
            ->rawColumns(['gross_salary', 'total_deductions', 'net_salary', 'actions'])
            ->toJson();
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
