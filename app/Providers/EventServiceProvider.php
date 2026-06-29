<?php

namespace App\Providers;

use App\Events\AgentExecutionCompleted;
use App\Events\AttendanceMarked;
use App\Events\BusArrived;
use App\Events\BusArriving;
use App\Events\ExamPublished;
use App\Events\FeeReminderGenerated;
use App\Events\HomeworkAssigned;
use App\Events\LocationUpdated;
use App\Events\TeacherAttendanceMarked;
use App\Events\TripCompleted;
use App\Events\TripStarted;
use App\Listeners\CreateDatabaseNotification;
use App\Listeners\LogNotificationActivity;
use App\Listeners\LogTransportActivity;
use App\Listeners\SendPushNotification;
use App\Listeners\SendTransportPushNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AttendanceMarked::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        TeacherAttendanceMarked::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        HomeworkAssigned::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        ExamPublished::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        FeeReminderGenerated::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        AgentExecutionCompleted::class => [
            CreateDatabaseNotification::class,
            SendPushNotification::class,
            LogNotificationActivity::class,
        ],
        // ── Transport Events ──
        LocationUpdated::class => [
            LogTransportActivity::class,
        ],
        BusArriving::class => [
            SendTransportPushNotification::class,
            LogTransportActivity::class,
        ],
        BusArrived::class => [
            SendTransportPushNotification::class,
            LogTransportActivity::class,
        ],
        TripStarted::class => [
            SendTransportPushNotification::class,
            LogTransportActivity::class,
        ],
        TripCompleted::class => [
            SendTransportPushNotification::class,
            LogTransportActivity::class,
        ],
    ];

    public function shouldBeDiscoverable(): bool
    {
        return false;
    }
}
