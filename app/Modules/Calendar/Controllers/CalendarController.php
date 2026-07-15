<?php

namespace App\Modules\Calendar\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Calendar\Requests\StoreCalendarRequest;
use App\Modules\Calendar\Requests\UpdateCalendarRequest;
use App\Modules\Calendar\Services\CalendarService;
use App\Modules\Calendar\Repositories\CalendarRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarRepositoryInterface $repository,
        private readonly CalendarService $service,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', \App\Modules\Calendar\Models\AcademicCalendar::class);

        return view('modules.calendar.index', [
            'eventTypes' => \App\Modules\Calendar\Models\AcademicCalendar::eventTypes(),
            'audiences' => \App\Modules\Calendar\Models\AcademicCalendar::audiences(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AcademicCalendar::class);

        $query = $this->repository->query($request->only([
            'event_type', 'audience', 'is_published', 'month', 'year', 'academic_year_id',
        ]));

        return DataTables::of($query)
            ->addColumn('event_type', fn (AcademicCalendar $event) => '<span class="badge ' . AcademicCalendar::badgeClass($event->event_type) . '">' . e($event->event_type_label) . '</span>')
            ->addColumn('audience', fn (AcademicCalendar $event) => '<span class="badge bg-info">' . e($event->audience_label) . '</span>')
            ->editColumn('start_date', fn (AcademicCalendar $event) => $event->start_date?->format('d M Y'))
            ->editColumn('end_date', fn (AcademicCalendar $event) => $event->end_date?->format('d M Y'))
            ->addColumn('date_range', fn (AcademicCalendar $event) => view('modules.calendar._date_range', ['event' => $event])->render())
            ->addColumn('status', fn (AcademicCalendar $event) => $event->is_published ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-warning">Draft</span>')
            ->addColumn('actions', fn (AcademicCalendar $event) => view('modules.calendar._actions', ['event' => $event])->render())
            ->rawColumns(['event_type', 'audience', 'date_range', 'status', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        $this->authorize('create', AcademicCalendar::class);

        return view('modules.calendar.create', [
            'eventTypes' => AcademicCalendar::eventTypes(),
            'audiences' => AcademicCalendar::audiences(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
        ]);
    }

    public function store(StoreCalendarRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicCalendar::class);

        $event = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Calendar event created successfully.',
            'event' => $event,
        ]);
    }

    public function show(AcademicCalendar $event): View
    {
        $this->authorize('view', $event);

        return view('modules.calendar.show', compact('event'));
    }

    public function edit(AcademicCalendar $event): View
    {
        $this->authorize('update', $event);

        return view('modules.calendar.edit', [
            'event' => $event,
            'eventTypes' => AcademicCalendar::eventTypes(),
            'audiences' => AcademicCalendar::audiences(),
            'academicYears' => AcademicYear::query()->orderByDesc('starts_on')->get(),
        ]);
    }

    public function update(UpdateCalendarRequest $request, AcademicCalendar $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event = $this->service->update($event->id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Calendar event updated successfully.',
            'event' => $event,
        ]);
    }

    public function destroy(AcademicCalendar $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $this->service->delete($event->id);

        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully.',
        ]);
    }

    public function togglePublish(AcademicCalendar $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event->update(['is_published' => !$event->is_published]);

        return response()->json([
            'success' => true,
            'message' => $event->is_published ? 'Event published.' : 'Event unpublished.',
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AcademicCalendar::class);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $events = AcademicCalendar::query()
            ->where('is_published', true)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->get()
            ->map(fn (AcademicCalendar $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date?->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'badge_class' => AcademicCalendar::badgeClass($event->event_type),
                'event_type_label' => $event->event_type_label,
                'audience' => $event->audience,
                'description' => $event->description,
                'location' => $event->location,
            ]);

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }
}
