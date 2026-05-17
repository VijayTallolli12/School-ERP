<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_section')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'excused'])->default('present');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'student_id', 'attendance_date']);
            $table->index(['school_id', 'class_section_id', 'attendance_date']);
            $table->index(['school_id', 'academic_year_id', 'attendance_date']);
            $table->index(['marked_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
