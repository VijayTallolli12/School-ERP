<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_timetable_slots', function (Blueprint $table): void {
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete()->after('subject_id');
            $table->unsignedTinyInteger('period_number')->nullable()->after('day_of_week');
            $table->time('start_time')->nullable()->after('period_number');
            $table->time('end_time')->nullable()->after('start_time');
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['academic_year_id', 'class_section_id', 'day_of_week', 'period_number'], 'timetable_period_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_timetable_slots', function (Blueprint $table): void {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex('timetable_period_lookup');
            $table->dropColumn([
                'academic_year_id',
                'period_number',
                'start_time',
                'end_time',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
