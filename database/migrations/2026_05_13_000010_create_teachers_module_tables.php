<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('employee_id', 50);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('gender', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('qualification', 150)->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'employee_id']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('teacher_subject', function (Blueprint $table): void {
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->primary(['teacher_id', 'subject_id']);
        });

        Schema::create('teacher_class_section', function (Blueprint $table): void {
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_section')->cascadeOnDelete();
            $table->boolean('is_class_teacher')->default(false)->index();
            $table->primary(['teacher_id', 'class_section_id']);
            $table->index(['class_section_id', 'is_class_teacher']);
        });

        Schema::create('teacher_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id', 'document_type']);
        });

        Schema::create('teacher_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->string('status', 30)->default('present')->index();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['teacher_id', 'attendance_date']);
        });

        Schema::create('teacher_leaves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_timetable_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_section')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->unsigned()->index();
            $table->string('period_label', 100);
            $table->string('room', 100)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['teacher_id', 'class_section_id', 'subject_id', 'day_of_week', 'period_label'], 'teacher_timetable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_timetable_slots');
        Schema::dropIfExists('teacher_leaves');
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('teacher_documents');
        Schema::dropIfExists('teacher_class_section');
        Schema::dropIfExists('teacher_subject');
        Schema::dropIfExists('teachers');
    }
};
