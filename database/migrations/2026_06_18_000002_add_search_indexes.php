<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('routes', function (Blueprint $table): void {
                $table->index(['school_id', 'route_name'], 'idx_routes_school_name');
                $table->index(['school_id', 'start_point'], 'idx_routes_school_start');
                $table->index(['school_id', 'end_point'], 'idx_routes_school_end');
            });
        } catch (\Exception) {}

        try {
            Schema::table('students', function (Blueprint $table): void {
                $table->fullText(['first_name', 'middle_name', 'last_name'], 'idx_students_name_fulltext');
            });
        } catch (\Exception) {}

        try {
            Schema::table('teachers', function (Blueprint $table): void {
                $table->fullText(['first_name', 'middle_name', 'last_name'], 'idx_teachers_name_fulltext');
            });
        } catch (\Exception) {}
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table): void {
            $table->dropIndex('idx_routes_school_name');
            $table->dropIndex('idx_routes_school_start');
            $table->dropIndex('idx_routes_school_end');
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropFullText('idx_students_name_fulltext');
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropFullText('idx_teachers_name_fulltext');
        });
    }
};
