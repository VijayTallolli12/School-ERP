<?php

namespace App\Modules\Leave\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Leave\Repositories\LeaveRequestRepositoryInterface;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveService
{
    public function __construct(
        private readonly LeaveRequestRepositoryInterface $leaveRequests,
        private readonly NotificationService $notifications,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function create(array $data): LeaveRequest
    {
        return DB::transaction(function () use ($data): LeaveRequest {
            $payload = $this->requestPayload($data);
            $payload['school_id'] = $this->schoolContext->id();
            $payload['user_id'] = auth()->id();
            $payload['days'] = $this->computeDays($data['from_date'], $data['to_date']);
            $payload['status'] = 'pending';
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();

            if ($attachment = $this->uploadAttachment($data)) {
                $payload['attachment'] = $attachment;
            }

            $leaveRequest = $this->leaveRequests->create($payload);

            $this->notifyAdmins($leaveRequest);

            return $leaveRequest;
        });
    }

    public function approve(LeaveRequest $leaveRequest, ?string $remarks = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $remarks): LeaveRequest {
            $leaveRequest = $this->leaveRequests->update($leaveRequest, [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $remarks,
                'updated_by' => auth()->id(),
            ]);

            $this->notifyUser($leaveRequest, 'approved');

            return $leaveRequest;
        });
    }

    public function reject(LeaveRequest $leaveRequest, ?string $remarks = null): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $remarks): LeaveRequest {
            $leaveRequest = $this->leaveRequests->update($leaveRequest, [
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $remarks,
                'updated_by' => auth()->id(),
            ]);

            $this->notifyUser($leaveRequest, 'rejected');

            return $leaveRequest;
        });
    }

    public function cancel(LeaveRequest $leaveRequest): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest): LeaveRequest {
            return $this->leaveRequests->update($leaveRequest, [
                'status' => 'cancelled',
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        DB::transaction(function () use ($leaveRequest): void {
            $this->deleteAttachment($leaveRequest);
            $this->leaveRequests->delete($leaveRequest);
        });
    }

    private function requestPayload(array $data): array
    {
        return Arr::only($data, [
            'student_id',
            'leave_type_id',
            'from_date',
            'to_date',
            'reason',
        ]);
    }

    private function computeDays(string $from, string $to): int
    {
        return Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->startOfDay()) + 1;
    }

    private function uploadAttachment(array $data): ?string
    {
        $file = $data['attachment'] ?? null;

        if (! ($file instanceof UploadedFile && $file->isValid())) {
            return null;
        }

        return $file->store('leave-requests', 'public');
    }

    private function deleteAttachment(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }
    }

    private function notifyAdmins(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing(['student', 'leaveType']);

        $this->notifications->create([
            'title' => 'New Leave Request',
            'message' => "{$leaveRequest->student?->full_name} submitted a {$leaveRequest->leaveType?->name} request.",
            'type' => 'attendance_alert',
            'priority' => 'medium',
            'status' => 'sent',
            'target_type' => 'admins',
            'channel' => 'in_app',
        ]);
    }

    private function notifyUser(LeaveRequest $leaveRequest, string $action): void
    {
        $leaveRequest->loadMissing(['student', 'leaveType']);

        $notification = $this->notifications->create([
            'title' => 'Leave Request ' . ucfirst($action),
            'message' => "Your {$leaveRequest->leaveType?->name} request for {$leaveRequest->student?->full_name} has been {$action}.",
            'type' => 'attendance_alert',
            'priority' => 'medium',
            'status' => 'sent',
            'target_type' => 'all',
            'channel' => 'in_app',
        ]);

        if ($leaveRequest->user_id) {
            $notification->users()->attach($leaveRequest->user_id, [
                'delivery_status' => 'delivered',
            ]);
        }
    }
}
