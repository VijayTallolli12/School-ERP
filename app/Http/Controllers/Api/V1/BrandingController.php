<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Modules\Settings\Services\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingController extends ApiBaseController
{
    public function __construct(private readonly BrandingService $branding) {}

    public function show(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        if (! $schoolId) {
            return $this->success($this->branding->defaults(), 'Default branding returned.');
        }

        $school = School::query()->find($schoolId);

        if (! $school) {
            return $this->success($this->branding->defaults(), 'School not found. Default branding returned.');
        }

        app(SchoolContext::class)->set($schoolId);

        return $this->success($this->branding->forSchool($school), 'Branding retrieved.');
    }

    private function resolveSchoolId(Request $request): ?int
    {
        $schoolId = (int) $request->header('X-School-Id', $request->input('school_id', 0));

        if ($schoolId <= 0) {
            return null;
        }

        return $schoolId;
    }
}
