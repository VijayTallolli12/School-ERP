<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['announcement', 'attendance_alert', 'fee_reminder', 'exam_result', 'general'])->default('general');
            $table->json('target_parents')->nullable(); // array of parent_ids or null for all
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['school_id', 'type']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_notifications');
    }
};