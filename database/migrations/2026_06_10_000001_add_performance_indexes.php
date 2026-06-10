<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('student_fee_items', function (Blueprint $table): void {
                $table->index(['student_fee_id', 'due_date'], 'idx_student_fee_items_fee_id_due');
            });
        } catch (\Exception) {}

        try {
            Schema::table('fee_payment_items', function (Blueprint $table): void {
                $table->index(['fee_payment_id', 'student_fee_item_id'], 'idx_fee_payment_items_payment_item');
            });
        } catch (\Exception) {}

        try {
            Schema::table('exam_results', function (Blueprint $table): void {
                $table->index(['school_id', 'exam_id', 'status'], 'idx_exam_results_school_exam_status');
                $table->index(['school_id', 'student_id', 'exam_id'], 'idx_exam_results_school_student_exam');
            });
        } catch (\Exception) {}

        try {
            Schema::table('homework', function (Blueprint $table): void {
                $table->index(['class_section_id', 'academic_year_id', 'status', 'due_date'], 'idx_homework_class_academic_status_due');
            });
        } catch (\Exception) {}

        try {
            Schema::table('teacher_attendances', function (Blueprint $table): void {
                $table->index(['teacher_id', 'attendance_date', 'status'], 'idx_teacher_attendances_teacher_date_status');
            });
        } catch (\Exception) {}
    }

    public function down(): void
    {
        Schema::table('student_fee_items', function (Blueprint $table): void {
            $table->dropIndex('idx_student_fee_items_fee_id_due');
        });

        Schema::table('fee_payment_items', function (Blueprint $table): void {
            $table->dropIndex('idx_fee_payment_items_payment_item');
        });

        Schema::table('exam_results', function (Blueprint $table): void {
            $table->dropIndex('idx_exam_results_school_exam_status');
            $table->dropIndex('idx_exam_results_school_student_exam');
        });

        Schema::table('homework', function (Blueprint $table): void {
            $table->dropIndex('idx_homework_class_academic_status_due');
        });

        Schema::table('teacher_attendances', function (Blueprint $table): void {
            $table->dropIndex('idx_teacher_attendances_teacher_date_status');
        });
    }
};
