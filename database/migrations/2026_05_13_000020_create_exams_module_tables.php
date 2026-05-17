<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_section')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('exam_name', 150);
            $table->string('exam_type', 100);
            $table->date('exam_date');
            $table->unsignedSmallInteger('maximum_marks')->default(100);
            $table->unsignedSmallInteger('pass_marks')->default(40);
            $table->string('status', 30)->default('scheduled')->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'class_section_id']);
            $table->index(['school_id', 'subject_id']);
        });

        Schema::create('exam_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedSmallInteger('marks_obtained')->default(0);
            $table->string('grade', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exams');
    }
};
