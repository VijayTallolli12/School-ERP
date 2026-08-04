<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fee_items', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('id');
        });

        $rows = DB::table('student_fee_items')
            ->join('student_fees', 'student_fee_items.student_fee_id', '=', 'student_fees.id')
            ->join('students', 'student_fees.student_id', '=', 'students.id')
            ->whereNull('student_fee_items.school_id')
            ->select('student_fee_items.id', 'students.school_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('student_fee_items')->where('id', $row->id)->update(['school_id' => $row->school_id]);
        }

        Schema::table('student_fee_items', function (Blueprint $table): void {
            $table->index(['school_id', 'student_fee_id'], 'idx_student_fee_items_school_fee');
        });
    }

    public function down(): void
    {
        Schema::table('student_fee_items', function (Blueprint $table): void {
            $table->dropIndex('idx_student_fee_items_school_fee');
            $table->dropColumn('school_id');
        });
    }
};
