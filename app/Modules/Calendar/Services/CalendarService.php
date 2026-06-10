<?php

namespace App\Modules\Calendar\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Calendar\Repositories\CalendarRepositoryInterface;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CalendarService
{
    public function __construct(
        private readonly CalendarRepositoryInterface $repository,
        private readonly NotificationService $notificationService,
    ) {}

    public function create(array $data): AcademicCalendar
    {
        return DB::transaction(function () use ($data): AcademicCalendar {
            $data['school_id'] = app(SchoolContext::class)->id();
            $data['created_by'] = auth()->id();

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): AcademicCalendar
    {
        return DB::transaction(function () use ($id, $data): AcademicCalendar {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($id));
    }

    public function togglePublish(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $isPublished = $this->repository->togglePublish($id);

            if ($isPublished) {
                $this->sendPublishNotifications($id);
            }

            return $isPublished;
        });
    }

    private function sendPublishNotifications(int $id): void
    {
        $event = $this->repository->find($id);

        if (!$event) {
            return;
        }

        $targetType = match ($event->audience) {
            'all' => 'all',
            'students' => 'students',
            'parents' => 'parents',
            'teachers' => 'teachers',
            'staff' => 'staff',
            default => 'all',
        };

        $this->notificationService->create([
            'title' => "Event: {$event->title}",
            'message' => "{$event->title} on {$event->start_date->format('d M Y')}" . ($event->location ? " at {$event->location}" : ''),
            'target_type' => $targetType,
            'type' => 'calendar_event',
            'priority' => 'normal',
            'status' => 'sent',
            'channel' => 'in_app',
        ]);
    }

    public function getUpcomingEvents(int $limit = 6): array
    {
        return $this->repository->getUpcoming($limit)->toArray();
    }

    public function getEventsForMonth(int $year, int $month): array
    {
        return $this->repository->getByMonth($year, $month)
            ->map(fn (AcademicCalendar $event): array => [
                'id' => $event->id,
                'title' => $event->title,
                'event_type' => $event->event_type,
                'event_type_label' => $event->event_type_label,
                'badge_class' => $event->event_type_badge,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date?->format('Y-m-d'),
                'description' => $event->description,
                'location' => $event->location,
                'audience' => $event->audience_label,
            ])
            ->values()
            ->all();
    }

    public function find(int $id): ?AcademicCalendar
    {
        return $this->repository->find($id);
    }
}
