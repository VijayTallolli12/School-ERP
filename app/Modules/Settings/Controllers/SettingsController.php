<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Settings\Requests\UpdateSettingsRequest;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index(): View
    {
        $school = $this->settings->currentSchool();

        return view('modules.settings.index', [
            'school' => $school,
            'academicYears' => AcademicYear::query()
                ->where('school_id', $school->id)
                ->orderByDesc('starts_on')
                ->get(),
            'timezones' => timezone_identifiers_list(),
            'currencies' => ['INR', 'USD', 'EUR', 'GBP', 'AED', 'SGD'],
            'dateFormats' => [
                'd-m-Y' => now()->format('d-m-Y'),
                'd/m/Y' => now()->format('d/m/Y'),
                'm/d/Y' => now()->format('m/d/Y'),
                'Y-m-d' => now()->format('Y-m-d'),
                'd M Y' => now()->format('d M Y'),
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settings->update($this->settings->currentSchool(), $request->validatedPayload());

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'reload' => true,
        ]);
    }
}
