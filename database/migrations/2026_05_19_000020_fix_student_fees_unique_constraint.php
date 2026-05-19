<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix H3: Add composite unique index including school_id on student_fees.
     *
     * The original migration (2024_01_05_000040) creates:
     *   UNIQUE (student_id, academic_year_id)
     *
     * This migration adds:
     *   UNIQUE (school_id, student_id, academic_year_id)
     *
     * On a fresh database, MySQL may tie the FK constraint index to the
     * original unique, so we do NOT drop it — we only add the new superset.
     * Both indexes coexist safely; the new one is the authoritative constraint.
     */
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table): void {
            $table->unique(['school_id', 'student_id', 'academic_year_id']);
        });
    }

    /**
     * Rollback: remove the composite unique index added by this migration.
     * The original (student_id, academic_year_id) unique is untouched.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table): void {
            $table->dropUnique('student_fees_school_id_student_id_academic_year_id_unique');
        });
    }
};