<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getConfig('driver');

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('" . $table . "')");
            foreach ($rows as $row) {
                if ((property_exists($row, 'name') && $row->name === $indexName)
                    || (property_exists($row, 'idx_name') && $row->idx_name === $indexName)) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'mysql') {
            return count(DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName])) > 0;
        }

        if ($driver === 'pgsql') {
            return count(DB::select('SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?', [$table, $indexName])) > 0;
        }

        return false;
    }

    private function createIndex(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    public function up(): void
    {
        $this->createIndex('students', ['school_id', 'status'], 'idx_students_school_status');
        $this->createIndex('teachers', ['school_id', 'status'], 'idx_teachers_school_status');
        $this->createIndex('attendances', ['class_section_id', 'attendance_date'], 'idx_attendances_class_date');
        $this->createIndex('attendances', ['student_id', 'attendance_date'], 'idx_attendances_student_date');
        $this->createIndex('student_fees', ['school_id', 'status'], 'idx_student_fees_school_status');
        $this->createIndex('fee_payments', ['school_id', 'paid_on'], 'idx_fee_payments_school_paid');
        $this->createIndex('exams', ['school_id', 'academic_year_id', 'class_section_id'], 'idx_exams_school_year_class');
        $this->createIndex('homework', ['class_section_id', 'academic_year_id', 'status', 'due_date'], 'idx_homework_class_academic_status_due');
        $this->createIndex('fee_payment_items', ['student_fee_item_id'], 'idx_fpi_student_fee_item');
        $this->createIndex('exam_results', ['school_id', 'exam_id', 'student_id'], 'idx_exam_results_school_exam_student');
        $this->createIndex('student_sessions', ['student_id', 'status'], 'idx_student_sessions_student_status');
        $this->createIndex('student_sessions', ['class_section_id', 'status'], 'idx_student_sessions_class_status');
        $this->createIndex('guardians', ['school_id', 'status'], 'idx_guardians_school_status');
        $this->createIndex('teacher_attendances', ['teacher_id', 'attendance_date', 'status'], 'idx_teacher_attendances_teacher_date_status');
        $this->createIndex('teacher_leaves', ['teacher_id', 'status'], 'idx_teacher_leaves_teacher_status');
        $this->createIndex('fee_receipt_sequences', ['school_id', 'academic_year_id'], 'idx_fee_receipt_school_year');
        $this->createIndex('notifications', ['target_type', 'status'], 'idx_notifications_target_status');
        $this->createIndex('login_activities', ['created_at'], 'idx_login_activities_created_at');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('students', 'idx_students_school_status');
        $this->dropIndexIfExists('teachers', 'idx_teachers_school_status');
        $this->dropIndexIfExists('attendances', 'idx_attendances_class_date');
        $this->dropIndexIfExists('attendances', 'idx_attendances_student_date');
        $this->dropIndexIfExists('student_fees', 'idx_student_fees_school_status');
        $this->dropIndexIfExists('fee_payments', 'idx_fee_payments_school_paid');
        $this->dropIndexIfExists('exams', 'idx_exams_school_year_class');
        $this->dropIndexIfExists('homework', 'idx_homework_class_academic_status_due');
        $this->dropIndexIfExists('fee_payment_items', 'idx_fpi_student_fee_item');
        $this->dropIndexIfExists('exam_results', 'idx_exam_results_school_exam_student');
        $this->dropIndexIfExists('student_sessions', 'idx_student_sessions_student_status');
        $this->dropIndexIfExists('student_sessions', 'idx_student_sessions_class_status');
        $this->dropIndexIfExists('guardians', 'idx_guardians_school_status');
        $this->dropIndexIfExists('teacher_attendances', 'idx_teacher_attendances_teacher_date_status');
        $this->dropIndexIfExists('teacher_leaves', 'idx_teacher_leaves_teacher_status');
        $this->dropIndexIfExists('fee_receipt_sequences', 'idx_fee_receipt_school_year');
        $this->dropIndexIfExists('notifications', 'idx_notifications_target_status');
        $this->dropIndexIfExists('login_activities', 'idx_login_activities_created_at');
    }
};