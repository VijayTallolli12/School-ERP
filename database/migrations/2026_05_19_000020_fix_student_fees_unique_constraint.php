<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix H3: student_fees unique constraint must include school_id.
     *
     * Before: UNIQUE (student_id, academic_year_id)
     * After:  UNIQUE (school_id, student_id, academic_year_id)
     *
     * The old constraint prevented two schools from assigning fees to a
     * student for the same academic year if their IDs happened to match.
     * The new constraint is a strict superset — all existing rows satisfy
     * it, and it correctly allows per-school uniqueness.
     */
    public function up(): void
    {
        Schema::table('student_fees', function (Blueprint $table): void {
            // MySQL convention: student_fees_student_id_academic_year_id_unique
            $table->dropUnique('student_fees_student_id_academic_year_id_unique');
            $table->unique(['school_id', 'student_id', 'academic_year_id']);
        });
    }

    /**
     * Rollback: restore the original (weaker) unique constraint.
     *
     * Safe — no data conflict possible. The old constraint is less strict,
     * so any rows valid under the new index are also valid under the old one.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table): void {
            $table->dropUnique('student_fees_school_id_student_id_academic_year_id_unique');
            $table->unique(['student_id', 'academic_year_id']);
        });
    }
};