<?php

namespace App\Modules\Dashboard\Services\DataCollectors;

use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Timetable\Services\TimetableService;
use Illuminate\Support\Facades\Cache;

class CalendarCollector
{
    public function upcomingEvents(int $schoolId, int $limit = 6): mixed
    {
        return Cache::remember("dashboard.calendar.events.{$schoolId}.{$limit}", 300, fn () =>
            AcademicCalendar::published()
                ->upcoming($limit)
                ->get(['id', 'title', 'event_type', 'start_date', 'end_date', 'location'])
        );
    }

    public function todaySchedulesCount(int $schoolId): int
    {
        return Cache::remember("dashboard.calendar.schedules.{$schoolId}", 60, fn () =>
            app(TimetableService::class)->todaySchedulesCount()
        );
    }

    public function activeClassCount(int $schoolId): int
    {
        return Cache::remember("dashboard.calendar.classes.{$schoolId}", 300, fn () =>
            app(TimetableService::class)->activeClassCount()
        );
    }

    public function timetableStats(int $schoolId): array
    {
        return [
            'today_schedules' => $this->todaySchedulesCount($schoolId),
            'active_classes' => $this->activeClassCount($schoolId),
        ];
    }
}
