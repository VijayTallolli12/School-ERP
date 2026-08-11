<?php

namespace Tests\Feature;

use App\Core\Tenant\SchoolContext;
use App\Models\School;
use App\Models\User;
use App\Modules\Calendar\Models\AcademicCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Academic Calendar fixes:
 *
 * 1. Edit flow — the list view's Edit button loads the event via
 *    GET /admin/calendar/{id} and expects a JSON payload with an `event`
 *    object (it previously returned a view that did not exist, so the
 *    modal could never open — published events included).
 *
 * 2. Calendar view — events stored with a NULL end_date (single-day
 *    events) must appear in GET /admin/calendar/events for their month.
 *    The previous query required end_date >= month start, which excluded
 *    every single-day event from the Calendar view.
 */
class CalendarModuleTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $admin;
    private AcademicCalendar $publishedEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->school = School::query()->where('code', 'DEMO')->firstOrFail();
        $this->admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
        app(SchoolContext::class)->set($this->school->id);

        $this->admin->assignRole('School Admin');

        $academicYearId = \App\Models\AcademicYear::query()
            ->where('school_id', $this->school->id)
            ->where('is_active', true)
            ->value('id');

        $this->publishedEvent = AcademicCalendar::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYearId,
            'title' => 'Independence Day Celebration',
            'event_type' => 'holiday',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => null,
            'description' => 'Flag hoisting ceremony.',
            'audience' => 'all',
            'location' => 'Main Ground',
            'is_published' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    private function actingAsAdmin()
    {
        return $this->actingAs($this->admin)->withSession(['school_id' => $this->school->id]);
    }

    public function test_edit_loads_published_event_as_json(): void
    {
        $this->actingAsAdmin()
            ->getJson(route('admin.calendar.show', $this->publishedEvent->id))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'event' => [
                    'id' => $this->publishedEvent->id,
                    'title' => 'Independence Day Celebration',
                    'event_type' => 'holiday',
                    'audience' => 'all',
                    'start_date' => now()->addDays(10)->toDateString(),
                    'end_date' => null,
                    'location' => 'Main Ground',
                    'description' => 'Flag hoisting ceremony.',
                ],
            ]);
    }

    public function test_published_event_can_be_updated_and_stays_published(): void
    {
        $newTitle = 'Independence Day Celebration (Updated)';

        $this->actingAsAdmin()
            ->putJson(route('admin.calendar.update', $this->publishedEvent->id), [
                'title' => $newTitle,
                'event_type' => 'holiday',
                'academic_year_id' => $this->publishedEvent->academic_year_id,
                'audience' => 'all',
                'start_date' => $this->publishedEvent->start_date->toDateString(),
                'end_date' => null,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame($newTitle, $this->publishedEvent->fresh()->title);
        $this->assertTrue($this->publishedEvent->fresh()->is_published);
    }

    public function test_toggle_publish_actually_toggles_the_event(): void
    {
        $this->actingAsAdmin()
            ->patchJson(route('admin.calendar.toggle-publish', $this->publishedEvent->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertFalse($this->publishedEvent->fresh()->is_published);

        $this->actingAsAdmin()
            ->patchJson(route('admin.calendar.toggle-publish', $this->publishedEvent->id))
            ->assertOk();

        $this->assertTrue($this->publishedEvent->fresh()->is_published);
    }

    public function test_single_day_published_event_appears_in_calendar_view(): void
    {
        $eventDate = $this->publishedEvent->start_date;

        $this->actingAsAdmin()
            ->getJson(route('admin.calendar.events', [
                'year' => $eventDate->year,
                'month' => $eventDate->month,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.id', $this->publishedEvent->id)
            ->assertJsonPath('events.0.title', 'Independence Day Celebration');
    }

    public function test_single_day_event_only_appears_in_its_own_month(): void
    {
        $eventDate = $this->publishedEvent->start_date;

        $wrongMonth = $eventDate->copy()->addMonths(1);

        $this->actingAsAdmin()
            ->getJson(route('admin.calendar.events', [
                'year' => $wrongMonth->year,
                'month' => $wrongMonth->month,
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'events');
    }

    public function test_draft_event_is_hidden_from_calendar_view(): void
    {
        $draft = AcademicCalendar::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->publishedEvent->academic_year_id,
            'title' => 'Draft Event',
            'event_type' => 'workshop',
            'start_date' => now()->addDays(12)->toDateString(),
            'end_date' => null,
            'description' => null,
            'audience' => 'all',
            'location' => null,
            'is_published' => false,
            'created_by' => $this->admin->id,
        ]);

        $eventDate = $draft->start_date;

        $this->actingAsAdmin()
            ->getJson(route('admin.calendar.events', [
                'year' => $eventDate->year,
                'month' => $eventDate->month,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.id', $this->publishedEvent->id);
    }

    public function test_event_with_end_date_in_range_appears_in_calendar_view(): void
    {
        $multiDay = AcademicCalendar::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->publishedEvent->academic_year_id,
            'title' => 'Annual Sports Week',
            'event_type' => 'sports_day',
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(22)->toDateString(),
            'description' => null,
            'audience' => 'all',
            'location' => 'Stadium',
            'is_published' => true,
            'created_by' => $this->admin->id,
        ]);

        $eventDate = $multiDay->start_date;

        $this->actingAsAdmin()
            ->getJson(route('admin.calendar.events', [
                'year' => $eventDate->year,
                'month' => $eventDate->month,
            ]))
            ->assertOk()
            ->assertJsonCount(2, 'events');
    }
}
