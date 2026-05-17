<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_section')->cascadeOnDelete();
            $table->string('roll_no', 30)->nullable();
            $table->date('joined_on')->nullable();
            $table->date('left_on')->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'student_id']);
            $table->unique(['school_id', 'academic_year_id', 'class_section_id', 'roll_no'], 'student_sessions_roll_unique');
            $table->index(['school_id', 'academic_year_id', 'class_section_id', 'status'], 'student_sessions_school_year_class_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_sessions');
    }
};
