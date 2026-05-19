<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix H4: Add school_id to teacher pivot tables for tenant isolation.
     *
     * teacher_subject and teacher_class_section are BelongsToMany pivot
     * tables used by the Teacher model. They currently have no school_id
     * column, meaning a raw SQL insert could link a teacher from School A
     * to a subject/class-section from School B.
     *
     * Approach:
     *  1. Add nullable school_id FK to both tables (nullable so sync/attach
     *     without a pivot model still works during transition).
     *  2. Backfill existing rows from the teachers table.
     *  3. Add indexes on school_id for query performance.
     *
     * Application-layer fix (separate code change): Pivot models with
     * BelongsToSchool trait will auto-populate school_id on new records.
     * See: app/Modules/Teachers/Models/TeacherSubjectPivot.php
     *      app/Modules/Teachers/Models/TeacherClassSectionPivot.php
     */
    public function up(): void
    {
        // --- teacher_subject ---
        Schema::table('teacher_subject', function (Blueprint $table): void {
            $table->foreignId('school_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('schools')
                ->cascadeOnDelete();
        });

        // Backfill: copy school_id from the teacher each pivot row belongs to
        DB::statement('
            UPDATE teacher_subject
            SET school_id = (
                SELECT school_id FROM teachers WHERE teachers.id = teacher_subject.teacher_id
            )
            WHERE school_id IS NULL
        ');

        Schema::table('teacher_subject', function (Blueprint $table): void {
            $table->index('school_id');
        });

        // --- teacher_class_section ---
        Schema::table('teacher_class_section', function (Blueprint $table): void {
            $table->foreignId('school_id')
                ->nullable()
                ->after('class_section_id')
                ->constrained('schools')
                ->cascadeOnDelete();
        });

        DB::statement('
            UPDATE teacher_class_section
            SET school_id = (
                SELECT school_id FROM teachers WHERE teachers.id = teacher_class_section.teacher_id
            )
            WHERE school_id IS NULL
        ');

        Schema::table('teacher_class_section', function (Blueprint $table): void {
            $table->index('school_id');
        });
    }

    /**
     * Rollback: remove school_id columns from both pivot tables.
     *
     * The column and its index/foreign key are dropped. No data loss
     * beyond the school_id denormalization itself. The primary keys
     * (teacher_id, subject_id) and (teacher_id, class_section_id)
     * are untouched throughout.
     */
    public function down(): void
    {
        Schema::table('teacher_class_section', function (Blueprint $table): void {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });

        Schema::table('teacher_subject', function (Blueprint $table): void {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};