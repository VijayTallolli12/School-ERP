<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_timetable_slots', function (Blueprint $table): void {
            if (! Schema::hasColumn('teacher_timetable_slots', 'school_id')) {
                $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_timetable_slots', function (Blueprint $table): void {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
