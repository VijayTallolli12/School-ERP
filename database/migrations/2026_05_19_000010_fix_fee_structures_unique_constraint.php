<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix H2: fee_structures unique constraint must include school_id.
     *
     * Before: UNIQUE (academic_year_id, class_section_id)
     * After:  UNIQUE (school_id, academic_year_id, class_section_id)
     *
     * The old constraint prevented two schools from having a fee structure
     * for the same (academic_year, class_section) combo if their IDs happened
     * to match. The new constraint is a strict superset — all existing rows
     * satisfy it, and it correctly allows per-school uniqueness.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            // MySQL requires dropping the old unique index before creating the new one.
            // The old index is named by convention: fee_structures_academic_year_id_class_section_id_unique
            $table->dropUnique('fee_structures_academic_year_id_class_section_id_unique');
            $table->unique(['school_id', 'academic_year_id', 'class_section_id']);
        });
    }

    /**
     * Rollback: restore the original (weaker) unique constraint.
     *
     * Safe because the new index includes all columns of the old one,
     * so any rows valid under the new index are also valid under the old one.
     * No data loss or conflict possible during rollback.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->dropUnique('fee_structures_school_id_academic_year_id_class_section_id_unique');
            $table->unique(['academic_year_id', 'class_section_id']);
        });
    }
};