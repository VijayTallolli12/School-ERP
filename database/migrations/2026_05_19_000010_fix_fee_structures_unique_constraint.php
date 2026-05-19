<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix H2: Add composite unique index including school_id on fee_structures.
     *
     * The original migration (2024_01_05_000020) creates:
     *   UNIQUE (academic_year_id, class_section_id)
     *
     * This migration adds:
     *   UNIQUE (school_id, academic_year_id, class_section_id)
     *
     * On a fresh database, MySQL may tie the FK constraint index to the
     * original unique, so we do NOT drop it — we only add the new superset.
     * Both indexes coexist safely; the new one is the authoritative constraint.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->unique(['school_id', 'academic_year_id', 'class_section_id']);
        });
    }

    /**
     * Rollback: remove the composite unique index added by this migration.
     * The original (academic_year_id, class_section_id) unique is untouched.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->dropUnique('fee_structures_school_id_academic_year_id_class_section_id_unique');
        });
    }
};