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
     * The index is explicitly named 'fs_school_year_section_unique' because the
     * auto-generated name (fee_structures_school_id_academic_year_id_class_section_id_unique)
     * is 66 characters — exceeding MySQL's 64-character identifier limit.
     */
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->unique(
                ['school_id', 'academic_year_id', 'class_section_id'],
                'fs_school_year_section_unique'
            );
        });
    }

    /**
     * Rollback: remove the composite unique index added by this migration.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table): void {
            $table->dropUnique('fs_school_year_section_unique');
        });
    }
};