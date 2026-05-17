<?php

namespace App\Modules\Notifications\Repositories;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface NotificationRepositoryInterface
{
    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function dataTableQuery(): Builder;

    public function markAsSent(Notification $notification): void;

    public function markAsFailed(Notification $notification): void;

    public function attachUsers(Notification $notification, array $userIds): void;

    public function dashboardStats(): array;

    public function bellQuery(int $userId): Builder;

    public function markUserRead(Notification $notification, int $userId): void;

    public function markAllUserRead(int $userId): void;

    public function announcementBannerQuery(): Builder;
}