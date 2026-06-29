<?php

// ────────────────────────────────────────────────────────────────────────────
// Notifications — api.v1.notifications.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use Illuminate\Support\Facades\Route;

Route::get('notifications', [NotificationApiController::class, 'index'])
    ->middleware('permission:notifications.view')->name('notifications.index');
Route::get('notifications/unread', [NotificationApiController::class, 'unread'])
    ->middleware('permission:notifications.view')->name('notifications.unread');
Route::post('notifications/{id}/read', [NotificationApiController::class, 'markRead'])
    ->middleware('permission:notifications.view')->name('notifications.read');
Route::post('notifications/read-all', [NotificationApiController::class, 'markAllRead'])
    ->middleware('permission:notifications.view')->name('notifications.read-all');
Route::get('notifications/announcements', [NotificationApiController::class, 'announcements'])
    ->middleware('permission:notifications.view')->name('notifications.announcements');

// ────────────────────────────────────────────────────────────────────────────
// Phase 5.4 — Real-Time Infrastructure
// ────────────────────────────────────────────────────────────────────────────

Route::post('devices/register', [DeviceController::class, 'register'])
    ->name('notifications.devices.register');
Route::post('devices/unregister', [DeviceController::class, 'unregister'])
    ->name('notifications.devices.unregister');
Route::get('notifications/unread-count', [DeviceController::class, 'unreadCount'])
    ->name('notifications.unread-count');
