<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Repositories\EmployeeRepositoryInterface;
use App\Modules\HR\Requests\StoreEmployeeRequest;
use App\Modules\HR\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeRepositoryInterface $employees,
        private readonly EmployeeService $service,
    ) {}

    public function index(): View
    {
        return view('modules.hr.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->employees->query())
            ->addColumn('full_name', fn (Employee $employee) => e($employee->full_name))
            ->addColumn('department', fn (Employee $employee) => e($employee->department?->name ?? '-'))
            ->addColumn('designation', fn (Employee $employee) => e($employee->designation?->name ?? '-'))
            ->addColumn('actions', fn (Employee $employee) => view('modules.hr._actions', compact('employee'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully.',
            'data' => $employee,
        ]);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['department', 'designation', 'reportingTo', 'contracts', 'documents']);

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee = $this->service->update($employee, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully.',
            'data' => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);
        $this->service->delete($employee);

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully.',
        ]);
    }
}
