<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->string('status', 20)->default('completed')->index();
            $table->string('void_reason', 500)->nullable();
            $table->foreignId('voided_by')->nullable()->after('collected_by')->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
        });

        Schema::table('fee_payment_items', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('id');
        });

        $rows = DB::table('fee_payment_items')
            ->join('fee_payments', 'fee_payment_items.fee_payment_id', '=', 'fee_payments.id')
            ->whereNull('fee_payment_items.school_id')
            ->select('fee_payment_items.id', 'fee_payments.school_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('fee_payment_items')->where('id', $row->id)->update(['school_id' => $row->school_id]);
        }

        Schema::table('fee_payment_items', function (Blueprint $table): void {
            $table->index('school_id', 'idx_fee_payment_items_school');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table): void {
            $table->dropForeign(['voided_by']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'void_reason', 'voided_by', 'voided_at']);
        });

        Schema::table('fee_payment_items', function (Blueprint $table): void {
            $table->dropIndex('idx_fee_payment_items_school');
            $table->dropColumn('school_id');
        });
    }
};
