<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('title', 200);
            $table->text('message');
            $table->string('type', 30)->index()->comment('attendance_alert, fee_reminder, exam_result_alert, announcement, timetable_update');
            $table->string('priority', 20)->default('medium')->comment('low, medium, high, urgent');
            $table->string('status', 20)->default('draft')->index()->comment('draft, sent, failed');
            $table->string('target_type', 20)->default('all')->comment('all, students, parents, teachers, admins');
            $table->string('channel', 20)->default('in_app')->comment('in_app, email, sms');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notification_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('delivery_status', 20)->default('pending')->comment('pending, delivered, failed');
            $table->timestamps();
            $table->unique(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user');
        Schema::dropIfExists('notifications');
    }
};