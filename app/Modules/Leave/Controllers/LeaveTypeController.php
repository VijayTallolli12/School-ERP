<?php

namespace App\Modules\Leave\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Leave\Repositories\LeaveTypeRepositoryInterface;
use App\Modules\Leave\Requests\StoreLeaveTypeRequest;
use App\Modules\Leave\Requests\UpdateLeaveTypeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LeaveTypeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly LeaveTypeRepositoryInterface $leaveTypes,
    ) {}

    public function index(): View
    {
        return view('modules.leave.types.index', [
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->leaveTypes->query())
            ->addColumn('status_label', fn (LeaveType $type) => $type->is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('actions', fn (LeaveType $type) => view('modules.leave.types._actions', compact('type'))->render())
            ->rawColumns(['status_label', 'actions'])
            ->toJson();
    }

    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $type = $this->leaveTypes->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Leave type created successfully.',
            'data' => $type,
        ]);
    }

    public function show(LeaveType $leaveType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leaveType->id,
                'name' => $leaveType->name,
                'description' => $leaveType->description,
                'is_active' => $leaveType->is_active,
            ],
        ]);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['updated_by'] = auth()->id();

        $type = $this->leaveTypes->update($leaveType, $data);

        return response()->json([
            'success' => true,
            'message' => 'Leave type updated successfully.',
            'data' => $type,
        ]);
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $this->authorize('delete', $leaveType);
        $this->leaveTypes->delete($leaveType);

        return response()->json([
            'success' => true,
            'message' => 'Leave type deleted successfully.',
        ]);
    }
}
